-- Additive seed for the canonical AdminPermissionCatalog.
-- Does NOT assign any permission to a role. Review target DB first.
-- Safe to rerun for rows that already exist by name or slug.
INSERT INTO admin_permissions (name, slug, description, module, action, status)
SELECT candidate.name, candidate.slug, candidate.description,
       candidate.module, candidate.action, candidate.status
FROM (
SELECT 'admin.dashboard.view' AS name, 'admin.dashboard.view' AS slug, 'View operational dashboard' AS description, 'dashboard' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.admins.view' AS name, 'admin.admins.view' AS slug, 'View admin accounts' AS description, 'admins' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.admins.manage' AS name, 'admin.admins.manage' AS slug, 'Create, update, disable and restore admin accounts' AS description, 'admins' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.roles.view' AS name, 'admin.roles.view' AS slug, 'View admin roles' AS description, 'roles' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.roles.manage' AS name, 'admin.roles.manage' AS slug, 'Create and update admin roles' AS description, 'roles' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.permissions.manage' AS name, 'admin.permissions.manage' AS slug, 'Manage role permission assignments' AS description, 'permissions' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.sessions.view' AS name, 'admin.sessions.view' AS slug, 'View admin session inventory' AS description, 'sessions' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.sessions.revoke' AS name, 'admin.sessions.revoke' AS slug, 'Revoke admin sessions' AS description, 'sessions' AS module, 'revoke' AS action, 'active' AS status
UNION ALL
SELECT 'admin.audit.view' AS name, 'admin.audit.view' AS slug, 'View administrative audit trail' AS description, 'audit' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.audit.export' AS name, 'admin.audit.export' AS slug, 'Export audit records' AS description, 'audit' AS module, 'export' AS action, 'active' AS status
UNION ALL
SELECT 'admin.notifications.view' AS name, 'admin.notifications.view' AS slug, 'View admin notifications' AS description, 'notifications' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.notifications.manage' AS name, 'admin.notifications.manage' AS slug, 'Create and acknowledge admin notifications' AS description, 'notifications' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.system_health.view' AS name, 'admin.system_health.view' AS slug, 'View system health indicators' AS description, 'system_health' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.incidents.manage' AS name, 'admin.incidents.manage' AS slug, 'Manage system incidents' AS description, 'incidents' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.settings.view' AS name, 'admin.settings.view' AS slug, 'View admin settings' AS description, 'settings' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.settings.manage' AS name, 'admin.settings.manage' AS slug, 'Update admin settings' AS description, 'settings' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.reports.view' AS name, 'admin.reports.view' AS slug, 'View business reports' AS description, 'reports' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.analytics.view' AS name, 'admin.analytics.view' AS slug, 'View product and operational analytics' AS description, 'analytics' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.products.view' AS name, 'admin.products.view' AS slug, 'View products' AS description, 'products' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.products.manage' AS name, 'admin.products.manage' AS slug, 'Create and update products' AS description, 'products' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.catalog.manage' AS name, 'admin.catalog.manage' AS slug, 'Manage categories, brands, attributes and specifications' AS description, 'catalog' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.orders.view' AS name, 'admin.orders.view' AS slug, 'View orders' AS description, 'orders' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.orders.manage' AS name, 'admin.orders.manage' AS slug, 'Perform permitted order state transitions' AS description, 'orders' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.payments.view' AS name, 'admin.payments.view' AS slug, 'View payment records' AS description, 'payments' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.refunds.manage' AS name, 'admin.refunds.manage' AS slug, 'Manage refund requests' AS description, 'refunds' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.inventory.view' AS name, 'admin.inventory.view' AS slug, 'View inventory and movements' AS description, 'inventory' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.inventory.manage' AS name, 'admin.inventory.manage' AS slug, 'Adjust inventory and reservations' AS description, 'inventory' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.shipping.manage' AS name, 'admin.shipping.manage' AS slug, 'Manage shipping and shipments' AS description, 'shipping' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.users.view' AS name, 'admin.users.view' AS slug, 'View customer accounts' AS description, 'users' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.users.manage' AS name, 'admin.users.manage' AS slug, 'Manage customer accounts within policy' AS description, 'users' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.reviews.manage' AS name, 'admin.reviews.manage' AS slug, 'Moderate reviews' AS description, 'reviews' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.questions.manage' AS name, 'admin.questions.manage' AS slug, 'Moderate product questions and answers' AS description, 'questions' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.content.view' AS name, 'admin.content.view' AS slug, 'View storefront content' AS description, 'content' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.content.manage' AS name, 'admin.content.manage' AS slug, 'Edit storefront content' AS description, 'content' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.content.publish' AS name, 'admin.content.publish' AS slug, 'Publish or schedule storefront content' AS description, 'content' AS module, 'publish' AS action, 'active' AS status
UNION ALL
SELECT 'admin.media.view' AS name, 'admin.media.view' AS slug, 'View media library' AS description, 'media' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.media.manage' AS name, 'admin.media.manage' AS slug, 'Upload and manage media assets' AS description, 'media' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.support.view' AS name, 'admin.support.view' AS slug, 'View support tickets' AS description, 'support' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.support.manage' AS name, 'admin.support.manage' AS slug, 'Assign and update support tickets' AS description, 'support' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.approvals.view' AS name, 'admin.approvals.view' AS slug, 'View sensitive-operation approval requests' AS description, 'approvals' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.approvals.decide' AS name, 'admin.approvals.decide' AS slug, 'Approve or reject sensitive operations' AS description, 'approvals' AS module, 'decide' AS action, 'active' AS status
UNION ALL
SELECT 'admin.bulk_operations.execute' AS name, 'admin.bulk_operations.execute' AS slug, 'Execute authorized bulk operations' AS description, 'bulk_operations' AS module, 'execute' AS action, 'active' AS status
UNION ALL
SELECT 'admin.saved_views.manage' AS name, 'admin.saved_views.manage' AS slug, 'Manage saved filters and views' AS description, 'saved_views' AS module, 'manage' AS action, 'active' AS status
UNION ALL
SELECT 'admin.security_events.view' AS name, 'admin.security_events.view' AS slug, 'View security events' AS description, 'security_events' AS module, 'view' AS action, 'active' AS status
UNION ALL
SELECT 'admin.security_events.manage' AS name, 'admin.security_events.manage' AS slug, 'Respond to security events' AS description, 'security_events' AS module, 'manage' AS action, 'active' AS status
) AS candidate
WHERE NOT EXISTS (
    SELECT 1
    FROM admin_permissions AS existing
    WHERE existing.name = candidate.name
       OR existing.slug = candidate.slug
);
