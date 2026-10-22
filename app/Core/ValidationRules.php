<?php

declare(strict_types=1);

final class ValidationRules
{
    public static function required(
        mixed $value,
        string $message = 'این فیلد الزامی است.'
    ): ?string {
        if ($value === null) {
            return $message;
        }

        if (is_string($value) && trim($value) === '') {
            return $message;
        }

        return null;
    }

    public static function string(
        mixed $value,
        string $message = 'مقدار باید متنی باشد.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        return null;
    }

    public static function length(
        mixed $value,
        int $min,
        ?int $max = null,
        string $message = 'طول مقدار نامعتبر است.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        $length = mb_strlen($value);

        if ($length < $min) {
            return $message;
        }

        if ($max !== null && $length > $max) {
            return $message;
        }

        return null;
    }

    public static function numeric(
        mixed $value,
        string $message = 'مقدار باید عددی باشد.'
    ): ?string {
        if (!is_numeric($value)) {
            return $message;
        }

        return null;
    }

    public static function email(
        mixed $value,
        string $message = 'فرمت ایمیل نامعتبر است.'
    ): ?string {
        if (
            !is_string($value) ||
            filter_var(
                $value,
                FILTER_VALIDATE_EMAIL
            ) === false
        ) {
            return $message;
        }

        return null;
    }

    public static function boolean(
        mixed $value,
        string $message = 'مقدار باید boolean باشد.'
    ): ?string {
        if (!is_bool($value)) {
            return $message;
        }

        return null;
    }

    public static function collect(
        array $rules
    ): array {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            if (!is_array($fieldRules)) {
                continue;
            }

            foreach ($fieldRules as $error) {
                if (!is_string($error)) {
                    continue;
                }

                $error = trim($error);

                if ($error === '') {
                    continue;
                }

                $errors[$field][] = $error;
            }

            if (
                isset($errors[$field]) &&
                count($errors[$field]) === 1
            ) {
                $errors[$field] =
                    $errors[$field][0];
            }
        }

        return $errors;
    }

    public static function nullable(
        mixed $value
    ): bool {
        return $value === null;
    }

    public static function integer(
        mixed $value,
        string $message = 'مقدار باید عدد صحیح باشد.'
    ): ?string {
        if (
            !is_int($value) &&
            !(
                is_string($value) &&
                filter_var($value, FILTER_VALIDATE_INT) !== false
            )
        ) {
            return $message;
        }

        return null;
    }

    public static function array(
        mixed $value,
        string $message = 'مقدار باید آرایه باشد.'
    ): ?string {
        if (!is_array($value)) {
            return $message;
        }

        return null;
    }

    public static function min(
        mixed $value,
        int|float $minimum,
        string $message = 'مقدار کمتر از حد مجاز است.'
    ): ?string {
        if (!is_numeric($value)) {
            return $message;
        }

        if ((float)$value < $minimum) {
            return $message;
        }

        return null;
    }

    public static function max(
        mixed $value,
        int|float $maximum,
        string $message = 'مقدار بیشتر از حد مجاز است.'
    ): ?string {
        if (!is_numeric($value)) {
            return $message;
        }

        if ((float)$value > $maximum) {
            return $message;
        }

        return null;
    }

    public static function in(
        mixed $value,
        array $allowed,
        string $message = 'مقدار انتخاب‌شده معتبر نیست.'
    ): ?string {
        if (!in_array($value, $allowed, true)) {
            return $message;
        }

        return null;
    }

    public static function same(
        mixed $value,
        mixed $other,
        string $message = 'مقادیر با یکدیگر مطابقت ندارند.'
    ): ?string {
        if ($value !== $other) {
            return $message;
        }

        return null;
    }

    public static function url(
        mixed $value,
        string $message = 'فرمت URL نامعتبر است.'
    ): ?string {
        if (
            !is_string($value) ||
            filter_var($value, FILTER_VALIDATE_URL) === false
        ) {
            return $message;
        }

        return null;
    }

    public static function regex(
        mixed $value,
        string $pattern,
        string $message = 'فرمت مقدار نامعتبر است.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        $result = preg_match($pattern, $value);

        if ($result !== 1) {
            return $message;
        }

        return null;
    }

    public static function filled(
        mixed $value,
        string $message = 'این فیلد نباید خالی باشد.'
    ): ?string {
        if ($value === null) {
            return $message;
        }

        if (is_string($value) && trim($value) === '') {
            return $message;
        }

        if (is_array($value) && $value === []) {
            return $message;
        }

        return null;
    }

    public static function sometimes(
        mixed $value,
        callable $rule
    ): ?string {
        if ($value === null) {
            return null;
        }

        $result = $rule($value);

        return is_string($result) && trim($result) !== ''
            ? $result
            : null;
    }

    public static function nullableRule(
        mixed $value,
        callable $rule
    ): ?string {
        if ($value === null) {
            return null;
        }

        $result = $rule($value);

        return is_string($result) && trim($result) !== ''
            ? $result
            : null;
    }

    public static function requiredIf(
        mixed $value,
        bool $condition,
        string $message = 'این فیلد در این شرایط الزامی است.'
    ): ?string {
        if (!$condition) {
            return null;
        }

        return self::required($value, $message);
    }

    public static function requiredWith(
        mixed $value,
        array $otherValues,
        string $message = 'این فیلد الزامی است.'
    ): ?string {
        foreach ($otherValues as $otherValue) {
            if (
                $otherValue !== null &&
                !(is_string($otherValue) && trim($otherValue) === '')
            ) {
                return self::required($value, $message);
            }
        }

        return null;
    }

    public static function requiredWithout(
        mixed $value,
        array $otherValues,
        string $message = 'این فیلد الزامی است.'
    ): ?string {
        foreach ($otherValues as $otherValue) {
            if (
                $otherValue !== null &&
                !(is_string($otherValue) && trim($otherValue) === '')
            ) {
                return null;
            }
        }

        return self::required($value, $message);
    }

    public static function different(
        mixed $value,
        mixed $other,
        string $message = 'مقدار باید با مقدار دیگر متفاوت باشد.'
    ): ?string {
        if ($value === $other) {
            return $message;
        }

        return null;
    }

    public static function booleanLike(
        mixed $value,
        string $message = 'مقدار boolean معتبر نیست.'
    ): ?string {
        if (is_bool($value)) {
            return null;
        }

        if (
            $value === 0 ||
            $value === 1 ||
            $value === '0' ||
            $value === '1' ||
            $value === 'true' ||
            $value === 'false'
        ) {
            return null;
        }

        return $message;
    }

    public static function apply(
        mixed $value,
        array $rules
    ): array {
        $errors = [];

        foreach ($rules as $rule) {
            if (!is_callable($rule)) {
                continue;
            }

            $result = $rule($value);

            if (
                is_string($result) &&
                trim($result) !== ''
            ) {
                $errors[] = trim($result);
            }
        }

        return array_values(array_unique($errors));
    }

    public static function minLength(
        mixed $value,
        int $minimum,
        string $message = 'طول مقدار کمتر از حد مجاز است.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        if (mb_strlen($value) < $minimum) {
            return $message;
        }

        return null;
    }

    public static function maxLength(
        mixed $value,
        int $maximum,
        string $message = 'طول مقدار بیشتر از حد مجاز است.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        if (mb_strlen($value) > $maximum) {
            return $message;
        }

        return null;
    }

    public static function betweenLength(
        mixed $value,
        int $minimum,
        int $maximum,
        string $message = 'طول مقدار خارج از محدوده مجاز است.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        $length = mb_strlen($value);

        if ($length < $minimum || $length > $maximum) {
            return $message;
        }

        return null;
    }

    public static function digits(
        mixed $value,
        int $length,
        string $message = 'تعداد ارقام نامعتبر است.'
    ): ?string {
        if (
            !is_string($value) &&
            !is_int($value)
        ) {
            return $message;
        }

        $value = (string)$value;

        if (
            !preg_match('/^[0-9]+$/', $value) ||
            strlen($value) !== $length
        ) {
            return $message;
        }

        return null;
    }

    public static function digitsBetween(
        mixed $value,
        int $minimum,
        int $maximum,
        string $message = 'تعداد ارقام خارج از محدوده مجاز است.'
    ): ?string {
        if (
            !is_string($value) &&
            !is_int($value)
        ) {
            return $message;
        }

        $value = (string)$value;

        if (!preg_match('/^[0-9]+$/', $value)) {
            return $message;
        }

        $length = strlen($value);

        if ($length < $minimum || $length > $maximum) {
            return $message;
        }

        return null;
    }

    public static function date(
        mixed $value,
        string $format = 'Y-m-d',
        string $message = 'تاریخ نامعتبر است.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        $date = DateTime::createFromFormat(
            $format,
            $value
        );

        if (
            $date === false ||
            $date->format($format) !== $value
        ) {
            return $message;
        }

        return null;
    }

    public static function dateAfter(
        mixed $value,
        string $date,
        string $format = 'Y-m-d',
        string $message = 'تاریخ باید بعد از تاریخ مشخص‌شده باشد.'
    ): ?string {
        if (
            !is_string($value) ||
            !is_string($date)
        ) {
            return $message;
        }

        $current = DateTime::createFromFormat(
            $format,
            $value
        );

        $target = DateTime::createFromFormat(
            $format,
            $date
        );

        if (
            $current === false ||
            $target === false ||
            $current <= $target
        ) {
            return $message;
        }

        return null;
    }

    public static function dateBefore(
        mixed $value,
        string $date,
        string $format = 'Y-m-d',
        string $message = 'تاریخ باید قبل از تاریخ مشخص‌شده باشد.'
    ): ?string {
        if (
            !is_string($value) ||
            !is_string($date)
        ) {
            return $message;
        }

        $current = DateTime::createFromFormat(
            $format,
            $value
        );

        $target = DateTime::createFromFormat(
            $format,
            $date
        );

        if (
            $current === false ||
            $target === false ||
            $current >= $target
        ) {
            return $message;
        }

        return null;
    }

    public static function confirmed(
        mixed $value,
        mixed $confirmation,
        string $message = 'تأیید مقدار با مقدار اصلی مطابقت ندارد.'
    ): ?string {
        if ($value !== $confirmation) {
            return $message;
        }

        return null;
    }

    public static function notIn(
        mixed $value,
        array $forbidden,
        string $message = 'مقدار انتخاب‌شده مجاز نیست.'
    ): ?string {
        if (in_array($value, $forbidden, true)) {
            return $message;
        }

        return null;
    }

    public static function notSame(
        mixed $value,
        mixed $other,
        string $message = 'مقادیر نباید یکسان باشند.'
    ): ?string {
        if ($value === $other) {
            return $message;
        }

        return null;
    }

    public static function arrayMin(
        mixed $value,
        int $minimum,
        string $message = 'تعداد آیتم‌ها کمتر از حد مجاز است.'
    ): ?string {
        if (!is_array($value)) {
            return $message;
        }

        if (count($value) < $minimum) {
            return $message;
        }

        return null;
    }

    public static function arrayMax(
        mixed $value,
        int $maximum,
        string $message = 'تعداد آیتم‌ها بیشتر از حد مجاز است.'
    ): ?string {
        if (!is_array($value)) {
            return $message;
        }

        if (count($value) > $maximum) {
            return $message;
        }

        return null;
    }

    public static function arrayBetween(
        mixed $value,
        int $minimum,
        int $maximum,
        string $message = 'تعداد آیتم‌ها خارج از محدوده مجاز است.'
    ): ?string {
        if (!is_array($value)) {
            return $message;
        }

        $count = count($value);

        if ($count < $minimum || $count > $maximum) {
            return $message;
        }

        return null;
    }

    public static function contains(
        mixed $value,
        mixed $expected,
        string $message = 'مقدار موردنظر در لیست وجود ندارد.'
    ): ?string {
        if (!is_array($value)) {
            return $message;
        }

        if (!in_array($expected, $value, true)) {
            return $message;
        }

        return null;
    }

    public static function uniqueArray(
        mixed $value,
        string $message = 'مقادیر آرایه باید یکتا باشند.'
    ): ?string {
        if (!is_array($value)) {
            return $message;
        }

        if (count($value) !== count(array_unique($value, SORT_REGULAR))) {
            return $message;
        }

        return null;
    }

    public static function each(
        mixed $value,
        callable $rule
    ): array {
        if (!is_array($value)) {
            return ['مقدار باید آرایه باشد.'];
        }

        $errors = [];

        foreach ($value as $key => $item) {
            $result = $rule($item);

            if (
                is_string($result) &&
                trim($result) !== ''
            ) {
                $errors[(string)$key] = trim($result);
            }
        }

        return $errors;
    }

    public static function requiredArray(
        mixed $value,
        string $message = 'این آرایه الزامی است و نباید خالی باشد.'
    ): ?string {
        if (!is_array($value) || $value === []) {
            return $message;
        }

        return null;
    }

    public static function alpha(
        mixed $value,
        string $message = 'مقدار باید فقط شامل حروف باشد.'
    ): ?string {
        if (
            !is_string($value) ||
            preg_match('/^[\p{L}]+$/u', $value) !== 1
        ) {
            return $message;
        }

        return null;
    }

    public static function alphaNumeric(
        mixed $value,
        string $message = 'مقدار باید فقط شامل حروف و اعداد باشد.'
    ): ?string {
        if (
            !is_string($value) ||
            preg_match('/^[\p{L}\p{N}]+$/u', $value) !== 1
        ) {
            return $message;
        }

        return null;
    }

    public static function digitsOnly(
        mixed $value,
        string $message = 'مقدار باید فقط شامل اعداد باشد.'
    ): ?string {
        if (
            !is_string($value) ||
            preg_match('/^[0-9]+$/', $value) !== 1
        ) {
            return $message;
        }

        return null;
    }

    public static function persianText(
        mixed $value,
        string $message = 'مقدار باید شامل حروف فارسی باشد.'
    ): ?string {
        if (
            !is_string($value) ||
            preg_match('/^[\x{0600}-\x{06FF}\s]+$/u', $value) !== 1
        ) {
            return $message;
        }

        return null;
    }

    public static function phone(
        mixed $value,
        string $message = 'شماره موبایل نامعتبر است.'
    ): ?string {
        if (
            !is_string($value) ||
            preg_match('/^09[0-9]{9}$/', $value) !== 1
        ) {
            return $message;
        }

        return null;
    }

    public static function slug(
        mixed $value,
        string $message = 'فرمت slug نامعتبر است.'
    ): ?string {
        if (
            !is_string($value) ||
            preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value) !== 1
        ) {
            return $message;
        }

        return null;
    }

    public static function ip(
        mixed $value,
        string $message = 'آدرس IP نامعتبر است.'
    ): ?string {
        if (
            !is_string($value) ||
            filter_var($value, FILTER_VALIDATE_IP) === false
        ) {
            return $message;
        }

        return null;
    }

    public static function json(
        mixed $value,
        string $message = 'JSON نامعتبر است.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        json_decode($value);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $message;
        }

        return null;
    }

    public static function sameType(
        mixed $value,
        mixed $other,
        string $message = 'نوع مقادیر باید یکسان باشد.'
    ): ?string {
        if (gettype($value) !== gettype($other)) {
            return $message;
        }

        return null;
    }

    public static function lowerThan(
        mixed $value,
        mixed $maximum,
        string $message = 'مقدار باید کمتر باشد.'
    ): ?string {
        if (!is_numeric($value) || !is_numeric($maximum)) {
            return $message;
        }

        if ((float)$value >= (float)$maximum) {
            return $message;
        }

        return null;
    }

    public static function greaterThan(
        mixed $value,
        mixed $minimum,
        string $message = 'مقدار باید بیشتر باشد.'
    ): ?string {
        if (!is_numeric($value) || !is_numeric($minimum)) {
            return $message;
        }

        if ((float)$value <= (float)$minimum) {
            return $message;
        }

        return null;
    }

    public static function between(
        mixed $value,
        int|float $minimum,
        int|float $maximum,
        string $message = 'مقدار خارج از محدوده مجاز است.'
    ): ?string {
        if (!is_numeric($value)) {
            return $message;
        }

        $number = (float)$value;

        if ($number < $minimum || $number > $maximum) {
            return $message;
        }

        return null;
    }

    public static function startsWith(
        mixed $value,
        string $prefix,
        string $message = 'مقدار با پیشوند موردنظر شروع نمی‌شود.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        if (!str_starts_with($value, $prefix)) {
            return $message;
        }

        return null;
    }

    public static function endsWith(
        mixed $value,
        string $suffix,
        string $message = 'مقدار با پسوند موردنظر تمام نمی‌شود.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        if (!str_ends_with($value, $suffix)) {
            return $message;
        }

        return null;
    }

    public static function containsText(
        mixed $value,
        string $needle,
        string $message = 'متن موردنظر در مقدار وجود ندارد.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        if (!str_contains($value, $needle)) {
            return $message;
        }

        return null;
    }

    public static function notContainsText(
        mixed $value,
        string $needle,
        string $message = 'مقدار شامل متن غیرمجاز است.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        if (str_contains($value, $needle)) {
            return $message;
        }

        return null;
    }

    public static function keyExists(
        mixed $value,
        string|int $key,
        string $message = 'کلید موردنظر وجود ندارد.'
    ): ?string {
        if (!is_array($value)) {
            return $message;
        }

        if (!array_key_exists($key, $value)) {
            return $message;
        }

        return null;
    }

    public static function keys(
        mixed $value,
        array $requiredKeys,
        string $message = 'برخی کلیدهای موردنیاز وجود ندارند.'
    ): ?string {
        if (!is_array($value)) {
            return $message;
        }

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $value)) {
                return $message;
            }
        }

        return null;
    }

    public static function notEmpty(
        mixed $value,
        string $message = 'مقدار نباید خالی باشد.'
    ): ?string {
        if (empty($value) && $value !== 0 && $value !== '0') {
            return $message;
        }

        return null;
    }

    public static function count(
        mixed $value,
        int $expected,
        string $message = 'تعداد آیتم‌ها نامعتبر است.'
    ): ?string {
        if (!is_array($value)) {
            return $message;
        }

        if (count($value) !== $expected) {
            return $message;
        }

        return null;
    }

    public static function hasAnyKey(
        mixed $value,
        array $keys,
        string $message = 'هیچ‌یک از کلیدهای موردنظر وجود ندارد.'
    ): ?string {
        if (!is_array($value)) {
            return $message;
        }

        foreach ($keys as $key) {
            if (array_key_exists($key, $value)) {
                return null;
            }
        }

        return $message;
    }

    public static function hasAllKeys(
        mixed $value,
        array $keys,
        string $message = 'برخی کلیدهای موردنظر وجود ندارند.'
    ): ?string {
        if (!is_array($value)) {
            return $message;
        }

        foreach ($keys as $key) {
            if (!array_key_exists($key, $value)) {
                return $message;
            }
        }

        return null;
    }

    public static function valueType(
        mixed $value,
        string $type,
        string $message = 'نوع مقدار نامعتبر است.'
    ): ?string {
        if (gettype($value) !== $type) {
            return $message;
        }

        return null;
    }

    public static function instanceOf(
        mixed $value,
        string $class,
        string $message = 'نوع شیء نامعتبر است.'
    ): ?string {
        if (!is_object($value) || !($value instanceof $class)) {
            return $message;
        }

        return null;
    }

    public static function callable(
        mixed $value,
        string $message = 'مقدار باید قابل اجرا باشد.'
    ): ?string {
        if (!is_callable($value)) {
            return $message;
        }

        return null;
    }

    public static function resource(
        mixed $value,
        string $message = 'مقدار باید resource باشد.'
    ): ?string {
        if (!is_resource($value)) {
            return $message;
        }

        return null;
    }

    public static function applyFirst(
        mixed $value,
        array $rules
    ): ?string {
        foreach ($rules as $rule) {
            if (!is_callable($rule)) {
                continue;
            }

            $result = $rule($value);

            if (
                is_string($result) &&
                trim($result) !== ''
            ) {
                return trim($result);
            }
        }

        return null;
    }

    public static function isNull(
        mixed $value,
        string $message = 'مقدار باید null باشد.'
    ): ?string {
        if ($value !== null) {
            return $message;
        }

        return null;
    }

    public static function notNull(
        mixed $value,
        string $message = 'مقدار نباید null باشد.'
    ): ?string {
        if ($value === null) {
            return $message;
        }

        return null;
    }

    public static function isObject(
        mixed $value,
        string $message = 'مقدار باید object باشد.'
    ): ?string {
        if (!is_object($value)) {
            return $message;
        }

        return null;
    }

    public static function isArray(
        mixed $value,
        string $message = 'مقدار باید array باشد.'
    ): ?string {
        if (!is_array($value)) {
            return $message;
        }

        return null;
    }

    public static function isString(
        mixed $value,
        string $message = 'مقدار باید string باشد.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        return null;
    }

    public static function isInteger(
        mixed $value,
        string $message = 'مقدار باید integer باشد.'
    ): ?string {
        if (!is_int($value)) {
            return $message;
        }

        return null;
    }

    public static function isFloat(
        mixed $value,
        string $message = 'مقدار باید float باشد.'
    ): ?string {
        if (!is_float($value)) {
            return $message;
        }

        return null;
    }

    public static function isBoolean(
        mixed $value,
        string $message = 'مقدار باید boolean باشد.'
    ): ?string {
        if (!is_bool($value)) {
            return $message;
        }

        return null;
    }

    public static function isNumeric(
        mixed $value,
        string $message = 'مقدار باید numeric باشد.'
    ): ?string {
        if (!is_numeric($value)) {
            return $message;
        }

        return null;
    }

    public static function isScalar(
        mixed $value,
        string $message = 'مقدار باید scalar باشد.'
    ): ?string {
        if (!is_scalar($value)) {
            return $message;
        }

        return null;
    }

    public static function isCallable(
        mixed $value,
        string $message = 'مقدار باید callable باشد.'
    ): ?string {
        if (!is_callable($value)) {
            return $message;
        }

        return null;
    }

    public static function isResource(
        mixed $value,
        string $message = 'مقدار باید resource باشد.'
    ): ?string {
        if (!is_resource($value)) {
            return $message;
        }

        return null;
    }

    public static function isIterable(
        mixed $value,
        string $message = 'مقدار باید قابل پیمایش باشد.'
    ): ?string {
        if (!is_iterable($value)) {
            return $message;
        }

        return null;
    }

    public static function isCountable(
        mixed $value,
        string $message = 'مقدار باید قابل شمارش باشد.'
    ): ?string {
        if (!is_countable($value)) {
            return $message;
        }

        return null;
    }

    public static function isFinite(
        mixed $value,
        string $message = 'مقدار باید یک عدد متناهی باشد.'
    ): ?string {
        if (!is_float($value) && !is_int($value)) {
            return $message;
        }

        if (!is_finite((float) $value)) {
            return $message;
        }

        return null;
    }

    public static function isInfinite(
        mixed $value,
        string $message = 'مقدار باید بی‌نهایت باشد.'
    ): ?string {
        if (!is_float($value) || !is_infinite($value)) {
            return $message;
        }

        return null;
    }

    public static function isNaN(
        mixed $value,
        string $message = 'مقدار باید NaN باشد.'
    ): ?string {
        if (!is_float($value) || !is_nan($value)) {
            return $message;
        }

        return null;
    }

    public static function isPositive(
        mixed $value,
        string $message = 'مقدار باید مثبت باشد.'
    ): ?string {
        if (!is_numeric($value) || $value <= 0) {
            return $message;
        }

        return null;
    }

    public static function isNegative(
        mixed $value,
        string $message = 'مقدار باید منفی باشد.'
    ): ?string {
        if (!is_numeric($value) || $value >= 0) {
            return $message;
        }

        return null;
    }

    public static function isNonNegative(
        mixed $value,
        string $message = 'مقدار باید صفر یا مثبت باشد.'
    ): ?string {
        if (!is_numeric($value) || $value < 0) {
            return $message;
        }

        return null;
    }

    public static function isNonPositive(
        mixed $value,
        string $message = 'مقدار باید صفر یا منفی باشد.'
    ): ?string {
        if (!is_numeric($value) || $value > 0) {
            return $message;
        }

        return null;
    }

    public static function isZero(
        mixed $value,
        string $message = 'مقدار باید صفر باشد.'
    ): ?string {
        if (!is_numeric($value) || $value != 0) {
            return $message;
        }

        return null;
    }

    public static function isNotZero(
        mixed $value,
        string $message = 'مقدار نباید صفر باشد.'
    ): ?string {
        if (!is_numeric($value) || $value == 0) {
            return $message;
        }

        return null;
    }

    public static function even(
        mixed $value,
        string $message = 'مقدار باید زوج باشد.'
    ): ?string {
        if (!is_numeric($value) || !is_int((int) $value) || ((int) $value % 2) !== 0) {
            return $message;
        }

        return null;
    }

    public static function odd(
        mixed $value,
        string $message = 'مقدار باید فرد باشد.'
    ): ?string {
        if (!is_numeric($value) || !is_int((int) $value) || ((int) $value % 2) === 0) {
            return $message;
        }

        return null;
    }

    public static function multipleOf(
        mixed $value,
        int|float $multiple,
        string $message = 'مقدار مضرب معتبر نیست.'
    ): ?string {
        if (!is_numeric($value) || $multiple == 0) {
            return $message;
        }

        $remainder = fmod((float) $value, (float) $multiple);

        if (abs($remainder) > PHP_FLOAT_EPSILON) {
            return $message;
        }

        return null;
    }

    public static function betweenExclusive(
        mixed $value,
        int|float $min,
        int|float $max,
        string $message = 'مقدار باید بین محدوده مشخص باشد.'
    ): ?string {
        if (
            !is_numeric($value) ||
            $value <= $min ||
            $value >= $max
        ) {
            return $message;
        }

        return null;
    }

    public static function isHex(
        mixed $value,
        string $message = 'مقدار باید hexadecimal معتبر باشد.'
    ): ?string {
        if (!is_string($value) || !preg_match('/^[0-9a-fA-F]+$/', $value)) {
            return $message;
        }

        return null;
    }

    public static function isBase64(
        mixed $value,
        string $message = 'مقدار باید Base64 معتبر باشد.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            return $message;
        }

        return null;
    }

    public static function isJsonObject(
        mixed $value,
        string $message = 'مقدار باید JSON object معتبر باشد.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        $decoded = json_decode($value, true);

        if (
            json_last_error() !== JSON_ERROR_NONE ||
            !is_array($decoded)
        ) {
            return $message;
        }

        return null;
    }

    public static function isJsonArray(
        mixed $value,
        string $message = 'مقدار باید JSON array معتبر باشد.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        $decoded = json_decode($value, true);

        if (
            json_last_error() !== JSON_ERROR_NONE ||
            !is_array($decoded) ||
            array_keys($decoded) !== range(0, count($decoded) - 1)
        ) {
            return $message;
        }

        return null;
    }

    public static function isDateTime(
        mixed $value,
        string $message = 'مقدار باید تاریخ و زمان معتبر باشد.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        try {
            new DateTime($value);
        } catch (Throwable) {
            return $message;
        }

        return null;
    }

    public static function isDateFormat(
        mixed $value,
        string $format,
        string $message = 'فرمت تاریخ نامعتبر است.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        $date = DateTime::createFromFormat($format, $value);

        if ($date === false) {
            return $message;
        }

        $errors = DateTime::getLastErrors();

        if (
            $errors !== false &&
            ($errors['warning_count'] > 0 || $errors['error_count'] > 0)
        ) {
            return $message;
        }

        if ($date->format($format) !== $value) {
            return $message;
        }

        return null;
    }

    public static function isTimeFormat(
        mixed $value,
        string $format = 'H:i:s',
        string $message = 'فرمت زمان نامعتبر است.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        return self::isDateFormat($value, $format, $message);
    }

    public static function isUuid(
        mixed $value,
        string $message = 'UUID نامعتبر است.'
    ): ?string {
        if (
            !is_string($value) ||
            !preg_match(
                '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/',
                $value
            )
        ) {
            return $message;
        }

        return null;
    }

    public static function isIpv4(
        mixed $value,
        string $message = 'IPv4 نامعتبر است.'
    ): ?string {
        if (
            !is_string($value) ||
            filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false
        ) {
            return $message;
        }

        return null;
    }

    public static function isIpv6(
        mixed $value,
        string $message = 'IPv6 نامعتبر است.'
    ): ?string {
        if (
            !is_string($value) ||
            filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false
        ) {
            return $message;
        }

        return null;
    }

    public static function isMacAddress(
        mixed $value,
        string $message = 'MAC Address نامعتبر است.'
    ): ?string {
        if (
            !is_string($value) ||
            !preg_match(
                '/^(?:[0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}$/',
                $value
            )
        ) {
            return $message;
        }

        return null;
    }

    public static function isEmail(
        mixed $value,
        string $message = 'آدرس ایمیل نامعتبر است.'
    ): ?string {
        if (
            !is_string($value) ||
            filter_var($value, FILTER_VALIDATE_EMAIL) === false
        ) {
            return $message;
        }

        return null;
    }

    public static function isUrl(
        mixed $value,
        string $message = 'URL نامعتبر است.'
    ): ?string {
        if (
            !is_string($value) ||
            filter_var($value, FILTER_VALIDATE_URL) === false
        ) {
            return $message;
        }

        return null;
    }

    public static function isDomain(
        mixed $value,
        string $message = 'نام دامنه نامعتبر است.'
    ): ?string {
        if (!is_string($value)) {
            return $message;
        }

        if (
            strlen($value) > 253 ||
            !preg_match(
                '/^(?=.{1,253}$)(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/',
                $value
            )
        ) {
            return $message;
        }

        return null;
    }

    public static function isPort(
        mixed $value,
        string $message = 'شماره پورت نامعتبر است.'
    ): ?string {
        if (
            !is_int($value) ||
            $value < 1 ||
            $value > 65535
        ) {
            return $message;
        }

        return null;
    }

    public static function isHttpMethod(
        mixed $value,
        string $message = 'HTTP Method نامعتبر است.'
    ): ?string {
        if (
            !is_string($value) ||
            !in_array(
                strtoupper($value),
                [
                    'GET',
                    'POST',
                    'PUT',
                    'PATCH',
                    'DELETE',
                    'HEAD',
                    'OPTIONS',
                ],
                true
            )
        ) {
            return $message;
        }

        return null;
    }

    public static function isMimeType(
        mixed $value,
        string $message = 'MIME Type نامعتبر است.'
    ): ?string {
        if (
            !is_string($value) ||
            !preg_match(
                '/^[a-zA-Z0-9!#$&^_.+-]+\/[a-zA-Z0-9!#$&^_.+-]+$/',
                $value
            )
        ) {
            return $message;
        }

        return null;
    }

    public static function isFilename(
        mixed $value,
        string $message = 'نام فایل نامعتبر است.'
    ): ?string {
        if (
            !is_string($value) ||
            trim($value) === '' ||
            preg_match('/[\\\\\/:*?"<>|]/', $value) ||
            $value === '.' ||
            $value === '..'
        ) {
            return $message;
        }

        return null;
    }

    public static function isLowercase(
        mixed $value,
        string $message = 'مقدار باید با حروف کوچک باشد.'
    ): ?string {
        if (!is_string($value) || $value !== mb_strtolower($value)) {
            return $message;
        }

        return null;
    }

    public static function isUppercase(
        mixed $value,
        string $message = 'مقدار باید با حروف بزرگ باشد.'
    ): ?string {
        if (!is_string($value) || $value !== mb_strtoupper($value)) {
            return $message;
        }

        return null;
    }

    public static function isAscii(
        mixed $value,
        string $message = 'مقدار باید فقط شامل کاراکترهای ASCII باشد.'
    ): ?string {
        if (!is_string($value) || !preg_match('/^[\x00-\x7F]*$/', $value)) {
            return $message;
        }

        return null;
    }

    public static function isPrintable(
        mixed $value,
        string $message = 'مقدار باید شامل کاراکترهای قابل چاپ باشد.'
    ): ?string {
        if (!is_string($value) || preg_match('/[^\x20-\x7E]/', $value)) {
            return $message;
        }

        return null;
    }

    public static function isWhitespace(
        mixed $value,
        string $message = 'مقدار باید فقط شامل فاصله باشد.'
    ): ?string {
        if (!is_string($value) || $value === '' || preg_match('/\S/', $value)) {
            return $message;
        }

        return null;
    }

    public static function isAlphanumeric(
        mixed $value,
        string $message = 'مقدار باید فقط شامل حروف و اعداد باشد.'
    ): ?string {
        if (!is_string($value) || !preg_match('/^[a-zA-Z0-9]+$/', $value)) {
            return $message;
        }

        return null;
    }

    public static function isLettersOnly(
        mixed $value,
        string $message = 'مقدار باید فقط شامل حروف باشد.'
    ): ?string {
        if (!is_string($value) || !preg_match('/^\p{L}+$/u', $value)) {
            return $message;
        }

        return null;
    }

    public static function isDigitsOnly(
        mixed $value,
        string $message = 'مقدار باید فقط شامل اعداد باشد.'
    ): ?string {
        if (!is_string($value) || !preg_match('/^[0-9]+$/', $value)) {
            return $message;
        }

        return null;
    }

    public static function isPersianDigits(
        mixed $value,
        string $message = 'مقدار باید فقط شامل ارقام فارسی باشد.'
    ): ?string {
        if (!is_string($value) || !preg_match('/^[۰-۹]+$/u', $value)) {
            return $message;
        }

        return null;
    }

    public static function isJsonScalar(
        mixed $value,
        string $message = 'مقدار باید JSON scalar معتبر باشد.'
    ): ?string {
        if (
            !is_null($value) &&
            !is_bool($value) &&
            !is_int($value) &&
            !is_float($value) &&
            !is_string($value)
        ) {
            return $message;
        }

        return null;
    }

    public static function isJsonSerializable(
        mixed $value,
        string $message = 'مقدار باید JSON serializable باشد.'
    ): ?string {
        if (
            !is_object($value) ||
            !($value instanceof JsonSerializable)
        ) {
            return $message;
        }

        return null;
    }

    public static function isSerializable(
        mixed $value,
        string $message = 'مقدار باید Serializable باشد.'
    ): ?string {
        if (
            !is_object($value) ||
            !($value instanceof Serializable)
        ) {
            return $message;
        }

        return null;
    }

    public static function isTraversable(
        mixed $value,
        string $message = 'مقدار باید Traversable باشد.'
    ): ?string {
        if (
            !is_object($value) ||
            !($value instanceof Traversable)
        ) {
            return $message;
        }

        return null;
    }

    public static function isDateObject(
        mixed $value,
        string $message = 'مقدار باید DateTimeInterface باشد.'
    ): ?string {
        if (
            !is_object($value) ||
            !($value instanceof DateTimeInterface)
        ) {
            return $message;
        }

        return null;
    }

    public static function isDateTimeImmutable(
        mixed $value,
        string $message = 'مقدار باید DateTimeImmutable باشد.'
    ): ?string {
        if (!($value instanceof DateTimeImmutable)) {
            return $message;
        }

        return null;
    }

    public static function isDateTimeMutable(
        mixed $value,
        string $message = 'مقدار باید DateTime باشد.'
    ): ?string {
        if (!($value instanceof DateTime)) {
            return $message;
        }

        return null;
    }

    public static function isException(
        mixed $value,
        string $message = 'مقدار باید Exception باشد.'
    ): ?string {
        if (!($value instanceof Throwable)) {
            return $message;
        }

        return null;
    }

    public static function isClosure(
        mixed $value,
        string $message = 'مقدار باید Closure باشد.'
    ): ?string {
        if (!($value instanceof Closure)) {
            return $message;
        }

        return null;
    }

    public static function isInterface(
        mixed $value,
        string $interface,
        string $message = 'مقدار باید نمونه‌ای از interface موردنظر باشد.'
    ): ?string {
        if (!is_object($value) || !interface_exists($interface) || !($value instanceof $interface)) {
            return $message;
        }

        return null;
    }

    public static function isTrait(
        mixed $value,
        string $trait,
        string $message = 'مقدار باید از trait موردنظر استفاده کند.'
    ): ?string {
        if (!is_object($value) || !trait_exists($trait)) {
            return $message;
        }

        $traits = class_uses($value);

        if (!in_array($trait, $traits, true)) {
            return $message;
        }

        return null;
    }

    public static function isClass(
        mixed $value,
        string $class,
        string $message = 'مقدار باید نمونه‌ای از کلاس موردنظر باشد.'
    ): ?string {
        if (!is_object($value) || !class_exists($class) || !($value instanceof $class)) {
            return $message;
        }

        return null;
    }

    public static function isEnum(
        mixed $value,
        string $enum,
        string $message = 'مقدار باید نمونه‌ای از Enum موردنظر باشد.'
    ): ?string {
        if (
            !is_object($value) ||
            !enum_exists($enum) ||
            !($value instanceof $enum)
        ) {
            return $message;
        }

        return null;
    }

    public static function isBackedEnum(
        mixed $value,
        string $enum,
        string $message = 'مقدار باید نمونه‌ای از Backed Enum باشد.'
    ): ?string {
        if (
            !is_object($value) ||
            !enum_exists($enum) ||
            !($value instanceof $enum) ||
            !($value instanceof BackedEnum)
        ) {
            return $message;
        }

        return null;
    }

    public static function isUnitEnum(
        mixed $value,
        string $enum,
        string $message = 'مقدار باید نمونه‌ای از Unit Enum باشد.'
    ): ?string {
        if (
            !is_object($value) ||
            !enum_exists($enum) ||
            !($value instanceof $enum) ||
            !($value instanceof UnitEnum)
        ) {
            return $message;
        }

        return null;
    }

    public static function isAnonymousClass(
        mixed $value,
        string $message = 'مقدار باید نمونه‌ای از کلاس anonymous باشد.'
    ): ?string {
        if (!is_object($value)) {
            return $message;
        }

        $reflection = new ReflectionClass($value);

        if (!$reflection->isAnonymous()) {
            return $message;
        }

        return null;
    }

    public static function isFinalClass(
        mixed $value,
        string $message = 'کلاس باید final باشد.'
    ): ?string {
        if (!is_object($value)) {
            return $message;
        }

        $reflection = new ReflectionClass($value);

        if (!$reflection->isFinal()) {
            return $message;
        }

        return null;
    }

    public static function isAbstractClass(
        mixed $value,
        string $message = 'کلاس باید abstract باشد.'
    ): ?string {
        if (!is_object($value)) {
            return $message;
        }

        $reflection = new ReflectionClass($value);

        if (!$reflection->isAbstract()) {
            return $message;
        }

        return null;
    }

    public static function isPublic(
        mixed $value,
        string $method,
        string $message = 'متد باید public باشد.'
    ): ?string {
        if (!is_object($value) || !method_exists($value, $method)) {
            return $message;
        }

        $reflection = new ReflectionMethod($value, $method);

        if (!$reflection->isPublic()) {
            return $message;
        }

        return null;
    }

    public static function isProtected(
        mixed $value,
        string $method,
        string $message = 'متد باید protected باشد.'
    ): ?string {
        if (!is_object($value) || !method_exists($value, $method)) {
            return $message;
        }

        $reflection = new ReflectionMethod($value, $method);

        if (!$reflection->isProtected()) {
            return $message;
        }

        return null;
    }

    public static function isPrivate(
        mixed $value,
        string $method,
        string $message = 'متد باید private باشد.'
    ): ?string {
        if (!is_object($value) || !method_exists($value, $method)) {
            return $message;
        }

        $reflection = new ReflectionMethod($value, $method);

        if (!$reflection->isPrivate()) {
            return $message;
        }

        return null;
    }

    public static function isStatic(
        mixed $value,
        string $method,
        string $message = 'متد باید static باشد.'
    ): ?string {
        if (!is_object($value) || !method_exists($value, $method)) {
            return $message;
        }

        $reflection = new ReflectionMethod($value, $method);

        if (!$reflection->isStatic()) {
            return $message;
        }

        return null;
    }

    public static function isFinalMethod(
        mixed $value,
        string $method,
        string $message = 'متد باید final باشد.'
    ): ?string {
        if (!is_object($value) || !method_exists($value, $method)) {
            return $message;
        }

        $reflection = new ReflectionMethod($value, $method);

        if (!$reflection->isFinal()) {
            return $message;
        }

        return null;
    }

    public static function isAbstractMethod(
        mixed $value,
        string $method,
        string $message = 'متد باید abstract باشد.'
    ): ?string {
        if (
            !is_object($value) ||
            (
                !method_exists($value, $method) &&
                !($value instanceof ReflectionClass)
            )
        ) {
            return $message;
        }

        try {
            $reflection = $value instanceof ReflectionClass
                ? $value
                : new ReflectionClass($value);

            if (!$reflection->hasMethod($method)) {
                return $message;
            }

            $reflectionMethod = $reflection->getMethod($method);

            if (!$reflectionMethod->isAbstract()) {
                return $message;
            }
        } catch (ReflectionException) {
            return $message;
        }

        return null;
    }
    public static function hasMethod(
        mixed $value,
        string $method,
        string $message = 'متد موردنظر وجود ندارد.'
    ): ?string {
        if (!is_object($value) || !method_exists($value, $method)) {
            return $message;
        }

        return null;
    }

    public static function hasProperty(
        mixed $value,
        string $property,
        string $message = 'Property موردنظر وجود ندارد.'
    ): ?string {
        if (!is_object($value) || !property_exists($value, $property)) {
            return $message;
        }

        return null;
    }

    public static function isReadableProperty(
        mixed $value,
        string $property,
        string $message = 'Property باید قابل خواندن باشد.'
    ): ?string {
        if (!is_object($value) || !property_exists($value, $property)) {
            return $message;
        }

        try {
            $reflection = new ReflectionProperty($value, $property);

            if (!$reflection->isPublic()) {
                return $message;
            }
        } catch (ReflectionException) {
            return $message;
        }

        return null;
    }

    public static function isWritableProperty(
        mixed $value,
        string $property,
        string $message = 'Property باید قابل نوشتن باشد.'
    ): ?string {
        if (!is_object($value) || !property_exists($value, $property)) {
            return $message;
        }

        try {
            $reflection = new ReflectionProperty($value, $property);

            if (!$reflection->isPublic() || $reflection->isReadOnly()) {
                return $message;
            }
        } catch (ReflectionException) {
            return $message;
        }

        return null;
    }

    public static function isReadonlyProperty(
        mixed $value,
        string $property,
        string $message = 'Property باید readonly باشد.'
    ): ?string {
        if (!is_object($value) || !property_exists($value, $property)) {
            return $message;
        }

        try {
            $reflection = new ReflectionProperty($value, $property);

            if (!$reflection->isReadOnly()) {
                return $message;
            }
        } catch (ReflectionException) {
            return $message;
        }

        return null;
    }

    public static function isStaticProperty(
        mixed $value,
        string $property,
        string $message = 'Property باید static باشد.'
    ): ?string {
        if (!is_object($value) || !property_exists($value, $property)) {
            return $message;
        }

        try {
            $reflection = new ReflectionProperty($value, $property);

            if (!$reflection->isStatic()) {
                return $message;
            }
        } catch (ReflectionException) {
            return $message;
        }

        return null;
    }

    public static function isPublicProperty(
        mixed $value,
        string $property,
        string $message = 'Property باید public باشد.'
    ): ?string {
        if (!is_object($value) || !property_exists($value, $property)) {
            return $message;
        }

        try {
            $reflection = new ReflectionProperty($value, $property);

            if (!$reflection->isPublic()) {
                return $message;
            }
        } catch (ReflectionException) {
            return $message;
        }

        return null;
    }

    public static function isProtectedProperty(
        mixed $value,
        string $property,
        string $message = 'Property باید protected باشد.'
    ): ?string {
        if (!is_object($value) || !property_exists($value, $property)) {
            return $message;
        }

        try {
            $reflection = new ReflectionProperty($value, $property);

            if (!$reflection->isProtected()) {
                return $message;
            }
        } catch (ReflectionException) {
            return $message;
        }

        return null;
    }

    public static function isPrivateProperty(
        mixed $value,
        string $property,
        string $message = 'Property باید private باشد.'
    ): ?string {
        if (!is_object($value) || !property_exists($value, $property)) {
            return $message;
        }

        try {
            $reflection = new ReflectionProperty($value, $property);

            if (!$reflection->isPrivate()) {
                return $message;
            }
        } catch (ReflectionException) {
            return $message;
        }

        return null;
    }

    public static function hasConstant(
        mixed $value,
        string $constant,
        string $message = 'Constant موردنظر وجود ندارد.'
    ): ?string {
        if (!is_object($value) || !defined(get_class($value) . '::' . $constant)) {
            return $message;
        }

        return null;
    }

    public static function isInstanceOfAny(
        mixed $value,
        array $classes,
        string $message = 'مقدار از هیچ‌یک از کلاس‌های موردنظر نیست.'
    ): ?string {
        if (!is_object($value)) {
            return $message;
        }

        foreach ($classes as $class) {
            if (
                is_string($class) &&
                (
                    (class_exists($class) && $value instanceof $class) ||
                    (interface_exists($class) && $value instanceof $class)
                )
            ) {
                return null;
            }
        }

        return $message;
    }

    public static function isPropertyInitialized(
        mixed $value,
        string $property,
        string $message = 'Property مقداردهی نشده است.'
    ): ?string {
        if (!is_object($value) || !property_exists($value, $property)) {
            return $message;
        }

        try {
            $reflection = new ReflectionProperty($value, $property);

            if (!$reflection->isInitialized($value)) {
                return $message;
            }
        } catch (ReflectionException) {
            return $message;
        }

        return null;
    }

    public static function isPropertyNullable(
        mixed $value,
        string $property,
        string $message = 'Property باید nullable باشد.'
    ): ?string {
        if (!is_object($value) || !property_exists($value, $property)) {
            return $message;
        }

        try {
            $reflection = new ReflectionProperty($value, $property);
            $type = $reflection->getType();

            if ($type === null || !$type->allowsNull()) {
                return $message;
            }
        } catch (ReflectionException) {
            return $message;
        }

        return null;
    }

    public static function isPropertyTyped(
        mixed $value,
        string $property,
        string $message = 'Property باید دارای type باشد.'
    ): ?string {
        if (!is_object($value) || !property_exists($value, $property)) {
            return $message;
        }

        try {
            $reflection = new ReflectionProperty($value, $property);

            if (!$reflection->hasType()) {
                return $message;
            }
        } catch (ReflectionException) {
            return $message;
        }

        return null;
    }

    public static function isMethodVariadic(
        mixed $value,
        string $method,
        string $message = 'متد باید variadic باشد.'
    ): ?string {
        if (!is_object($value) || !method_exists($value, $method)) {
            return $message;
        }

        try {
            $reflection = new ReflectionMethod($value, $method);

            if (!$reflection->isVariadic()) {
                return $message;
            }
        } catch (ReflectionException) {
            return $message;
        }

        return null;
    }

    public static function isMethodConstructor(
        mixed $value,
        string $method,
        string $message = 'متد باید constructor باشد.'
    ): ?string {
        if (!is_object($value) || !method_exists($value, $method)) {
            return $message;
        }

        try {
            $reflection = new ReflectionMethod($value, $method);

            if (!$reflection->isConstructor()) {
                return $message;
            }
        } catch (ReflectionException) {
            return $message;
        }

        return null;
    }

    public static function isMethodDestructor(
        mixed $value,
        string $method,
        string $message = 'متد باید destructor باشد.'
    ): ?string {
        if (!is_object($value) || !method_exists($value, $method)) {
            return $message;
        }

        try {
            $reflection = new ReflectionMethod($value, $method);

            if (!$reflection->isDestructor()) {
                return $message;
            }
        } catch (ReflectionException) {
            return $message;
        }

        return null;
    }

    public static function hasParameter(
        mixed $value,
        string $method,
        string $parameter,
        string $message = 'پارامتر موردنظر وجود ندارد.'
    ): ?string {
        if (!is_object($value) || !method_exists($value, $method)) {
            return $message;
        }

        try {
            $reflection = new ReflectionMethod($value, $method);

            foreach ($reflection->getParameters() as $parameterInfo) {
                if ($parameterInfo->getName() === $parameter) {
                    return null;
                }
            }
        } catch (ReflectionException) {
            return $message;
        }

        return $message;
    }

    public static function isParameterOptional(
        mixed $value,
        string $method,
        string $parameter,
        string $message = 'پارامتر باید optional باشد.'
    ): ?string {
        if (!is_object($value) || !method_exists($value, $method)) {
            return $message;
        }

        try {
            $reflection = new ReflectionMethod($value, $method);

            foreach ($reflection->getParameters() as $parameterInfo) {
                if ($parameterInfo->getName() === $parameter) {
                    return $parameterInfo->isOptional()
                        ? null
                        : $message;
                }
            }
        } catch (ReflectionException) {
            return $message;
        }

        return $message;
    }

    public static function isParameterVariadic(
        mixed $value,
        string $method,
        string $parameter,
        string $message = 'پارامتر باید variadic باشد.'
    ): ?string {
        if (!is_object($value) || !method_exists($value, $method)) {
            return $message;
        }

        try {
            $reflection = new ReflectionMethod($value, $method);

            foreach ($reflection->getParameters() as $parameterInfo) {
                if ($parameterInfo->getName() === $parameter) {
                    return $parameterInfo->isVariadic()
                        ? null
                        : $message;
                }
            }
        } catch (ReflectionException) {
            return $message;
        }

        return $message;
    }
}
