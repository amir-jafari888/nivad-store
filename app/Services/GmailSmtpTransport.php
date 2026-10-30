<?php

declare(strict_types=1);

/**
 * Minimal Gmail SMTP transport using PHP streams only.
 *
 * No network request occurs until send() is explicitly called. Credentials
 * are read exclusively from process environment and are never returned or
 * logged. This class sends one message; durable retries belong to the
 * database-backed outbox worker, which remains gated until schema approval.
 */
final class GmailSmtpTransport
{
    private const DEFAULT_HOST = 'smtp.gmail.com';
    private const DEFAULT_PORT = 587;
    private const TIMEOUT_SECONDS = 12;

    /**
     * @return array{success:bool, provider:string, message_id:?string, error_code:?string}
     */
    public function send(
        string $recipientEmail,
        string $subject,
        string $bodyText,
        ?string $recipientName = null
    ): array {
        $config = $this->configuration();

        if ($config === null) {
            return $this->failure('smtp_not_configured');
        }

        if (filter_var($recipientEmail, FILTER_VALIDATE_EMAIL) === false) {
            return $this->failure('invalid_recipient');
        }

        $subject = trim($subject);
        $bodyText = trim($bodyText);
        $recipientName = $recipientName !== null ? trim($recipientName) : null;

        if (
            $subject === '' ||
            mb_strlen($subject) > 255 ||
            $bodyText === '' ||
            mb_strlen($bodyText) > 50000
        ) {
            return $this->failure('invalid_message');
        }

        if (
            $this->containsHeaderBreak($subject) ||
            $this->containsHeaderBreak($recipientName ?? '') ||
            ($recipientName !== null && mb_strlen($recipientName) > 120)
        ) {
            return $this->failure('invalid_header');
        }

        $timeout = max(3, min(30, (int) (getenv('NIVAD_GMAIL_SMTP_TIMEOUT') ?: self::TIMEOUT_SECONDS)));
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
                'peer_name' => $config['host'],
            ],
        ]);

        $remote = ($config['port'] === 465 ? 'ssl://' : 'tcp://')
            . $config['host'] . ':' . $config['port'];

        $errno = 0;
        $error = '';
        $socket = @stream_socket_client(
            $remote,
            $errno,
            $error,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!is_resource($socket)) {
            return $this->failure('smtp_connection_failed');
        }

        stream_set_timeout($socket, $timeout);

        try {
            $this->expect($socket, [220]);
            $hostname = gethostname() ?: 'localhost';
            $hostname = preg_replace('/[^A-Za-z0-9.-]/', '', $hostname) ?: 'localhost';

            $this->command($socket, 'EHLO ' . $hostname, [250]);

            if ($config['port'] !== 465) {
                $this->command($socket, 'STARTTLS', [220]);

                $crypto = @stream_socket_enable_crypto(
                    $socket,
                    true,
                    STREAM_CRYPTO_METHOD_TLS_CLIENT
                );

                if ($crypto !== true) {
                    return $this->failure('smtp_tls_failed');
                }

                $this->command($socket, 'EHLO ' . $hostname, [250]);
            }

            $this->command($socket, 'AUTH LOGIN', [334]);
            $this->command($socket, base64_encode($config['username']), [334]);
            $this->command($socket, base64_encode($config['password']), [235]);

            $this->command($socket, 'MAIL FROM:<' . $config['username'] . '>', [250]);
            $this->command($socket, 'RCPT TO:<' . $recipientEmail . '>', [250, 251]);
            $this->command($socket, 'DATA', [354]);

            $messageId = '<' . bin2hex(random_bytes(16)) . '@gmail-smtp.nivad.local>';
            $fromName = $this->safeDisplayName(
                $this->environmentValue('NIVAD_GMAIL_FROM_NAME') ?: 'Nivad Admin'
            );
            $toHeader = $recipientName !== null && trim($recipientName) !== ''
                ? $this->encodeHeader($this->safeDisplayName($recipientName)) . ' <' . $recipientEmail . '>'
                : $recipientEmail;

            $headers = [
                'From: ' . $this->encodeHeader($fromName) . ' <' . $config['username'] . '>',
                'To: ' . $toHeader,
                'Subject: ' . $this->encodeHeader(trim($subject)),
                'Date: ' . gmdate('D, d M Y H:i:s') . ' +0000',
                'Message-ID: ' . $messageId,
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: 8bit',
                'X-Mailer: Nivad Gmail SMTP transport',
            ];

            $body = str_replace(["\r\n", "\r"], "\n", $bodyText);
            $body = str_replace("\n", "\r\n", $body);
            $body = preg_replace('/(?m)^\./', '..', $body) ?? $body;
            $payload = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.\r\n";

            $this->write($socket, $payload);
            $this->expect($socket, [250]);
            $this->command($socket, 'QUIT', [221]);

            return [
                'success' => true,
                'provider' => 'gmail_smtp',
                'message_id' => $messageId,
                'error_code' => null,
            ];
        } catch (Throwable $exception) {
            // Deliberately return a stable code rather than SMTP transcript,
            // which may contain addresses or provider diagnostics.
            return $this->failure('smtp_delivery_failed');
        } finally {
            if (is_resource($socket)) {
                fclose($socket);
            }
        }
    }

    private function configuration(): ?array
    {
        $username = $this->environmentValue('NIVAD_GMAIL_SMTP_USER');
        $password = $this->environmentValue('NIVAD_GMAIL_SMTP_APP_PASSWORD');
        $host = $this->environmentValue('NIVAD_GMAIL_SMTP_HOST') ?: self::DEFAULT_HOST;
        $portValue = $this->environmentValue('NIVAD_GMAIL_SMTP_PORT') ?: (string) self::DEFAULT_PORT;

        if (
            $username === '' ||
            $password === '' ||
            filter_var($username, FILTER_VALIDATE_EMAIL) === false ||
            strtolower($host) !== self::DEFAULT_HOST ||
            !in_array($portValue, ['465', '587'], true)
        ) {
            return null;
        }

        return [
            'username' => $username,
            'password' => $password,
            'host' => self::DEFAULT_HOST,
            'port' => (int) $portValue,
        ];
    }

    private function command($socket, string $command, array $expectedCodes): string
    {
        $this->write($socket, $command . "\r\n");

        return $this->expect($socket, $expectedCodes);
    }

    private function write($socket, string $data): void
    {
        $length = strlen($data);
        $written = 0;

        while ($written < $length) {
            $count = @fwrite($socket, substr($data, $written));
            if ($count === false || $count === 0) {
                throw new RuntimeException('SMTP write failed.');
            }
            $written += $count;
        }
    }

    private function expect($socket, array $expectedCodes): string
    {
        $response = '';
        $code = null;

        do {
            $line = @fgets($socket, 515);
            if ($line === false) {
                throw new RuntimeException('SMTP response unavailable.');
            }

            $response .= $line;
            if (preg_match('/^(\d{3})([ -])/', $line, $matches) !== 1) {
                throw new RuntimeException('Invalid SMTP response.');
            }

            $code = (int) $matches[1];
            $continue = $matches[2] === '-';
        } while ($continue);

        if (!in_array($code, $expectedCodes, true)) {
            throw new RuntimeException('Unexpected SMTP response.');
        }

        return $response;
    }

    private function encodeHeader(string $value): string
    {
        if (preg_match('/[^\x20-\x7E]/', $value) === 1) {
            return '=?UTF-8?B?' . base64_encode($value) . '?=';
        }

        return $value;
    }

    private function safeDisplayName(string $value): string
    {
        $value = trim(preg_replace('/[\r\n]+/', ' ', $value) ?? '');

        return mb_substr($value, 0, 120);
    }

    private function containsHeaderBreak(string $value): bool
    {
        return preg_match('/[\r\n\x00]/', $value) === 1;
    }

    private function environmentValue(string $name): string
    {
        $value = getenv($name);

        return is_string($value) ? trim($value) : '';
    }

    private function failure(string $errorCode): array
    {
        return [
            'success' => false,
            'provider' => 'gmail_smtp',
            'message_id' => null,
            'error_code' => $errorCode,
        ];
    }
}
