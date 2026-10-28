<?php

declare(strict_types=1);

/**
 * Nivad Admin Role/Permission Audit.
 * READ ONLY: SELECT statements only; no INSERT, UPDATE, DELETE, or schema changes.
 * Run from the project root in the configured XAMPP/PHP environment:
 * php test-admin-role-audit.php
 */

require_once __DIR__ . '/app/Core/Bootstrap.php';
Bootstrap::boot();

function roleAuditOut(string $text): void
{
    echo htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . PHP_EOL;
}

try {
    $db = Database::connection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $rolesStmt = $db->query(
        'SELECT
            ar.id,
            ar.name,
            ar.slug,
            ar.description,
            ar.status,
            COUNT(DISTINCT arp.permission_id) AS permission_count,
            COUNT(DISTINCT aur.admin_user_id) AS assigned_admin_count
         FROM admin_roles AS ar
         LEFT JOIN admin_role_permissions AS arp
            ON arp.role_id = ar.id
         LEFT JOIN admin_user_roles AS aur
            ON aur.role_id = ar.id
         WHERE ar.deleted_at IS NULL
         GROUP BY ar.id, ar.name, ar.slug, ar.description, ar.status
         ORDER BY ar.id'
    );
    $roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);

    roleAuditOut('NIVAD ADMIN ROLE AUDIT — READ ONLY');
    roleAuditOut('Active and inactive roles excluding soft-deleted rows: ' . count($roles));

    $permissionsStmt = $db->prepare(
        'SELECT
            ap.name,
            ap.slug,
            ap.description,
            ap.module,
            ap.action,
            ap.status
         FROM admin_role_permissions AS arp
         INNER JOIN admin_permissions AS ap
            ON ap.id = arp.permission_id
         WHERE arp.role_id = :role_id
           AND ap.deleted_at IS NULL
         ORDER BY ap.module, ap.action, ap.name'
    );

    $adminsStmt = $db->prepare(
        'SELECT
            au.id,
            au.first_name,
            au.last_name,
            au.status
         FROM admin_user_roles AS aur
         INNER JOIN admin_users AS au
            ON au.id = aur.admin_user_id
         WHERE aur.role_id = :role_id
           AND au.deleted_at IS NULL
         ORDER BY au.id'
    );

    foreach ($roles as $role) {
        roleAuditOut('');
        roleAuditOut(
            'ROLE #' . (int)$role['id'] .
            ' | ' . (string)$role['name'] .
            ' | slug=' . (string)$role['slug'] .
            ' | status=' . (string)$role['status'] .
            ' | permissions=' . (int)$role['permission_count'] .
            ' | assigned admins=' . (int)$role['assigned_admin_count']
        );
        roleAuditOut('Description: ' . (string)($role['description'] ?? '—'));

        $permissionsStmt->execute(['role_id' => (int)$role['id']]);
        $permissions = $permissionsStmt->fetchAll(PDO::FETCH_ASSOC);
        roleAuditOut('Permissions (' . count($permissions) . '):');
        if ($permissions === []) {
            roleAuditOut('  (none assigned)');
        }
        foreach ($permissions as $permission) {
            roleAuditOut(
                '  - ' . (string)$permission['name'] .
                ' | slug=' . (string)$permission['slug'] .
                ' | module=' . (string)($permission['module'] ?? '—') .
                ' | action=' . (string)($permission['action'] ?? '—') .
                ' | status=' . (string)$permission['status']
            );
        }

        $adminsStmt->execute(['role_id' => (int)$role['id']]);
        $admins = $adminsStmt->fetchAll(PDO::FETCH_ASSOC);
        roleAuditOut('Assigned admin accounts (' . count($admins) . '):');
        if ($admins === []) {
            roleAuditOut('  (none assigned)');
        }
        foreach ($admins as $admin) {
            $fullName = trim((string)($admin['first_name'] ?? '') . ' ' . (string)($admin['last_name'] ?? ''));
            roleAuditOut(
                '  - ID=' . (int)$admin['id'] .
                ' | name=' . ($fullName !== '' ? $fullName : 'نام ثبت نشده') .
                ' | status=' . (string)$admin['status']
            );
        }
    }

    $permissionCount = (int)$db->query(
        'SELECT COUNT(*) FROM admin_permissions WHERE deleted_at IS NULL'
    )->fetchColumn();
    roleAuditOut('');
    roleAuditOut('Non-deleted permission definitions in database: ' . $permissionCount);
    roleAuditOut('No changes were made to the database.');
} catch (Throwable $exception) {
    http_response_code(2);
    roleAuditOut('BLOCKED: role inventory could not be read from the configured database.');
    roleAuditOut('Reason: ' . $exception->getMessage());
    roleAuditOut('No changes were made to the database.');
    exit(2);
}
