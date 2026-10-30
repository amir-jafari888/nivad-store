<?php

declare(strict_types=1);

require_once __DIR__ . '/app/Services/AdminAccountPolicy.php';
require_once __DIR__ . '/app/Services/AdminSessionPolicy.php';
require_once __DIR__ . '/app/Services/AdminAuditDiffService.php';
require_once __DIR__ . '/app/Services/AdminSearchPolicy.php';
require_once __DIR__ . '/app/Services/AdminReportPolicy.php';
require_once __DIR__ . '/app/Services/AdminApprovalPolicy.php';
require_once __DIR__ . '/app/Services/AdminIncidentPolicy.php';
require_once __DIR__ . '/app/Services/AdminContentRevisionPolicy.php';
require_once __DIR__ . '/app/Services/AdminMediaLibraryPolicy.php';
require_once __DIR__ . '/app/Services/AdminEmailQueuePolicy.php';

$pass = 0;
$fail = 0;
function check(bool $condition, string $label): void {
    global $pass, $fail;
    if ($condition) { $pass++; echo "<span style=\"color:green\">[PASS]</span> {$label}<br>\n"; }
    else { $fail++; echo "<span style=\"color:red\">[FAIL]</span> {$label}<br>\n"; }
}
function throws(callable $fn): bool { try { $fn(); return false; } catch (Throwable $e) { return true; } }

check(AdminAccountPolicy::validate(['email'=>'Admin@Example.com','name'=>'Main Admin','role'=>'catalog-admin'])['email'] === 'admin@example.com', 'account normalization');
check(throws(fn() => AdminAccountPolicy::validate(['email'=>'bad','name'=>'A','role'=>'x'])), 'account validation rejects invalid email');
check(AdminSessionPolicy::normalize(['session_id'=>'sess_1','admin_id'=>2,'last_activity'=>time()-10,'timeout'=>1800])['online'] === true, 'session online calculation');
check(throws(fn() => AdminSessionPolicy::assertRevocationTarget(2,2)), 'session policy blocks self revocation');
check(AdminAuditDiffService::diff(['name'=>'A','password'=>'x'], ['name'=>'B','password'=>'y'], ['password'])['name']['after'] === 'B', 'audit diff captures changed field');
check(AdminAuditDiffService::diff(['password'=>'x'], ['password'=>'y'], ['password'])['password']['before'] === '[REDACTED]', 'audit diff redacts sensitive field');
check(AdminSearchPolicy::normalize(['query'=>'phone','page'=>2,'limit'=>50,'scope'=>'products'])['limit'] === 50, 'search normalization');
check(throws(fn() => AdminSearchPolicy::normalize(['query'=>'x','scope'=>'sql'])), 'search scope allowlist');
check(AdminReportPolicy::normalizeGroup('month') === 'month', 'report grouping allowlist');
check(throws(fn() => AdminReportPolicy::normalizeRange(['from'=>'2026-01-01','to'=>'2028-01-01'])), 'report range bound');
check(AdminApprovalPolicy::validateDecision(1,2,'approve')['decision'] === 'approve', 'approval decision validation');
check(throws(fn() => AdminApprovalPolicy::validateDecision(1,2,'reject','')), 'approval rejection reason required');
check(AdminIncidentPolicy::normalize(['title'=>'Outage','severity'=>'high','description'=>'test'])['severity'] === 'high', 'incident normalization');
check(throws(fn() => AdminIncidentPolicy::assertTransition('closed','open')), 'incident transition guard');
check(AdminContentRevisionPolicy::normalize(['entity_type'=>'product','entity_id'=>3,'status'=>'draft','payload'=>[]])['status'] === 'draft', 'content revision normalization');
check(throws(fn() => AdminContentRevisionPolicy::assertTransition('published','draft')), 'published content cannot silently revert');
check(AdminMediaLibraryPolicy::validateMetadata(['name'=>'x.webp','mime_type'=>'image/webp','size'=>100,'sha256'=>str_repeat('a',64)])['mime_type'] === 'image/webp', 'media metadata validation');
check(throws(fn() => AdminMediaLibraryPolicy::validateMetadata(['name'=>'x.exe','mime_type'=>'application/octet-stream','size'=>100,'sha256'=>str_repeat('a',64)])), 'media MIME allowlist');
check(AdminEmailQueuePolicy::nextDelaySeconds(0) === 30, 'email retry backoff baseline');
check(AdminEmailQueuePolicy::normalize(['recipient'=>'admin@example.com','subject'=>'Test','body'=>'Body'])['status'] === 'pending', 'email queue normalization');

$total = $pass + $fail;
echo "<strong>Admin capability policy regression: {$pass} PASS, {$fail} FAIL, {$total} TOTAL</strong><br>\n";
exit($fail === 0 ? 0 : 1);
