-- Nivad Admin route-level authorization
-- MariaDB 10.4 compatible. Apply this migration BEFORE deploying routes/api.php.
-- Idempotent: existing permission rows and assignments are preserved.
-- This migration grants the current Super Admin role the existing API route permissions.
-- It does not grant new permissions to any other role.

START TRANSACTION;

INSERT INTO admin_permissions (
    name,
    slug,
    description,
    module,
    action,
    status
)
SELECT
    requested.name,
    requested.slug,
    CONCAT('Route authorization: ', requested.name),
    requested.module,
    requested.action,
    'active'
FROM (
SELECT 'admin.attributes.index' AS name, 'admin-attributes-index' AS slug, 'admin.attributes.index' AS description, 'admin.attributes' AS module, 'index' AS action
UNION ALL
SELECT 'admin.attributes.show' AS name, 'admin-attributes-show' AS slug, 'admin.attributes.show' AS description, 'admin.attributes' AS module, 'show' AS action
UNION ALL
SELECT 'admin.attributes.store' AS name, 'admin-attributes-store' AS slug, 'admin.attributes.store' AS description, 'admin.attributes' AS module, 'store' AS action
UNION ALL
SELECT 'admin.attributes.update' AS name, 'admin-attributes-update' AS slug, 'admin.attributes.update' AS description, 'admin.attributes' AS module, 'update' AS action
UNION ALL
SELECT 'admin.attributes.destroy' AS name, 'admin-attributes-destroy' AS slug, 'admin.attributes.destroy' AS description, 'admin.attributes' AS module, 'destroy' AS action
UNION ALL
SELECT 'admin.attribute-values.index' AS name, 'admin-attribute-values-index' AS slug, 'admin.attribute-values.index' AS description, 'admin.attribute-values' AS module, 'index' AS action
UNION ALL
SELECT 'admin.attribute-values.show' AS name, 'admin-attribute-values-show' AS slug, 'admin.attribute-values.show' AS description, 'admin.attribute-values' AS module, 'show' AS action
UNION ALL
SELECT 'admin.attribute-values.store' AS name, 'admin-attribute-values-store' AS slug, 'admin.attribute-values.store' AS description, 'admin.attribute-values' AS module, 'store' AS action
UNION ALL
SELECT 'admin.attribute-values.update' AS name, 'admin-attribute-values-update' AS slug, 'admin.attribute-values.update' AS description, 'admin.attribute-values' AS module, 'update' AS action
UNION ALL
SELECT 'admin.attribute-values.destroy' AS name, 'admin-attribute-values-destroy' AS slug, 'admin.attribute-values.destroy' AS description, 'admin.attribute-values' AS module, 'destroy' AS action
UNION ALL
SELECT 'admin.variants.show' AS name, 'admin-variants-show' AS slug, 'admin.variants.show' AS description, 'admin.variants' AS module, 'show' AS action
UNION ALL
SELECT 'admin.products.variants' AS name, 'admin-products-variants' AS slug, 'admin.products.variants' AS description, 'admin.products' AS module, 'variants' AS action
UNION ALL
SELECT 'admin.variants.store' AS name, 'admin-variants-store' AS slug, 'admin.variants.store' AS description, 'admin.variants' AS module, 'store' AS action
UNION ALL
SELECT 'admin.variants.update' AS name, 'admin-variants-update' AS slug, 'admin.variants.update' AS description, 'admin.variants' AS module, 'update' AS action
UNION ALL
SELECT 'admin.variants.destroy' AS name, 'admin-variants-destroy' AS slug, 'admin.variants.destroy' AS description, 'admin.variants' AS module, 'destroy' AS action
UNION ALL
SELECT 'admin.variant-attributes.index' AS name, 'admin-variant-attributes-index' AS slug, 'admin.variant-attributes.index' AS description, 'admin.variant-attributes' AS module, 'index' AS action
UNION ALL
SELECT 'admin.variant-attributes.store' AS name, 'admin-variant-attributes-store' AS slug, 'admin.variant-attributes.store' AS description, 'admin.variant-attributes' AS module, 'store' AS action
UNION ALL
SELECT 'admin.variant-attributes.destroy' AS name, 'admin-variant-attributes-destroy' AS slug, 'admin.variant-attributes.destroy' AS description, 'admin.variant-attributes' AS module, 'destroy' AS action
UNION ALL
SELECT 'admin.attributes.active' AS name, 'admin-attributes-active' AS slug, 'admin.attributes.active' AS description, 'admin.attributes' AS module, 'active' AS action
UNION ALL
SELECT 'admin.attributes.restore' AS name, 'admin-attributes-restore' AS slug, 'admin.attributes.restore' AS description, 'admin.attributes' AS module, 'restore' AS action
UNION ALL
SELECT 'admin.attributes.status' AS name, 'admin-attributes-status' AS slug, 'admin.attributes.status' AS description, 'admin.attributes' AS module, 'status' AS action
UNION ALL
SELECT 'admin.attribute-values.active' AS name, 'admin-attribute-values-active' AS slug, 'admin.attribute-values.active' AS description, 'admin.attribute-values' AS module, 'active' AS action
UNION ALL
SELECT 'admin.attribute-values.restore' AS name, 'admin-attribute-values-restore' AS slug, 'admin.attribute-values.restore' AS description, 'admin.attribute-values' AS module, 'restore' AS action
UNION ALL
SELECT 'admin.attribute-values.status' AS name, 'admin-attribute-values-status' AS slug, 'admin.attribute-values.status' AS description, 'admin.attribute-values' AS module, 'status' AS action
UNION ALL
SELECT 'admin.products.variants.active' AS name, 'admin-products-variants-active' AS slug, 'admin.products.variants.active' AS description, 'admin.products.variants' AS module, 'active' AS action
UNION ALL
SELECT 'admin.products.variant-default' AS name, 'admin-products-variant-default' AS slug, 'admin.products.variant-default' AS description, 'admin.products' AS module, 'variant-default' AS action
UNION ALL
SELECT 'admin.variants.default.set' AS name, 'admin-variants-default-set' AS slug, 'admin.variants.default.set' AS description, 'admin.variants.default' AS module, 'set' AS action
UNION ALL
SELECT 'admin.variants.default.clear' AS name, 'admin-variants-default-clear' AS slug, 'admin.variants.default.clear' AS description, 'admin.variants.default' AS module, 'clear' AS action
UNION ALL
SELECT 'admin.variants.restore' AS name, 'admin-variants-restore' AS slug, 'admin.variants.restore' AS description, 'admin.variants' AS module, 'restore' AS action
UNION ALL
SELECT 'admin.variants.status' AS name, 'admin-variants-status' AS slug, 'admin.variants.status' AS description, 'admin.variants' AS module, 'status' AS action
UNION ALL
SELECT 'admin.products.index' AS name, 'admin-products-index' AS slug, 'admin.products.index' AS description, 'admin.products' AS module, 'index' AS action
UNION ALL
SELECT 'admin.products.show' AS name, 'admin-products-show' AS slug, 'admin.products.show' AS description, 'admin.products' AS module, 'show' AS action
UNION ALL
SELECT 'admin.products.store' AS name, 'admin-products-store' AS slug, 'admin.products.store' AS description, 'admin.products' AS module, 'store' AS action
UNION ALL
SELECT 'admin.products.update' AS name, 'admin-products-update' AS slug, 'admin.products.update' AS description, 'admin.products' AS module, 'update' AS action
UNION ALL
SELECT 'admin.products.destroy' AS name, 'admin-products-destroy' AS slug, 'admin.products.destroy' AS description, 'admin.products' AS module, 'destroy' AS action
UNION ALL
SELECT 'admin.products.active' AS name, 'admin-products-active' AS slug, 'admin.products.active' AS description, 'admin.products' AS module, 'active' AS action
UNION ALL
SELECT 'admin.products.restore' AS name, 'admin-products-restore' AS slug, 'admin.products.restore' AS description, 'admin.products' AS module, 'restore' AS action
UNION ALL
SELECT 'admin.products.status' AS name, 'admin-products-status' AS slug, 'admin.products.status' AS description, 'admin.products' AS module, 'status' AS action
UNION ALL
SELECT 'admin.categories.index' AS name, 'admin-categories-index' AS slug, 'admin.categories.index' AS description, 'admin.categories' AS module, 'index' AS action
UNION ALL
SELECT 'admin.categories.active' AS name, 'admin-categories-active' AS slug, 'admin.categories.active' AS description, 'admin.categories' AS module, 'active' AS action
UNION ALL
SELECT 'admin.categories.show' AS name, 'admin-categories-show' AS slug, 'admin.categories.show' AS description, 'admin.categories' AS module, 'show' AS action
UNION ALL
SELECT 'admin.categories.store' AS name, 'admin-categories-store' AS slug, 'admin.categories.store' AS description, 'admin.categories' AS module, 'store' AS action
UNION ALL
SELECT 'admin.categories.update' AS name, 'admin-categories-update' AS slug, 'admin.categories.update' AS description, 'admin.categories' AS module, 'update' AS action
UNION ALL
SELECT 'admin.categories.destroy' AS name, 'admin-categories-destroy' AS slug, 'admin.categories.destroy' AS description, 'admin.categories' AS module, 'destroy' AS action
UNION ALL
SELECT 'admin.categories.restore' AS name, 'admin-categories-restore' AS slug, 'admin.categories.restore' AS description, 'admin.categories' AS module, 'restore' AS action
UNION ALL
SELECT 'admin.categories.status' AS name, 'admin-categories-status' AS slug, 'admin.categories.status' AS description, 'admin.categories' AS module, 'status' AS action
UNION ALL
SELECT 'admin.brands.index' AS name, 'admin-brands-index' AS slug, 'admin.brands.index' AS description, 'admin.brands' AS module, 'index' AS action
UNION ALL
SELECT 'admin.brands.active' AS name, 'admin-brands-active' AS slug, 'admin.brands.active' AS description, 'admin.brands' AS module, 'active' AS action
UNION ALL
SELECT 'admin.brands.show' AS name, 'admin-brands-show' AS slug, 'admin.brands.show' AS description, 'admin.brands' AS module, 'show' AS action
UNION ALL
SELECT 'admin.brands.store' AS name, 'admin-brands-store' AS slug, 'admin.brands.store' AS description, 'admin.brands' AS module, 'store' AS action
UNION ALL
SELECT 'admin.brands.update' AS name, 'admin-brands-update' AS slug, 'admin.brands.update' AS description, 'admin.brands' AS module, 'update' AS action
UNION ALL
SELECT 'admin.brands.destroy' AS name, 'admin-brands-destroy' AS slug, 'admin.brands.destroy' AS description, 'admin.brands' AS module, 'destroy' AS action
UNION ALL
SELECT 'admin.brands.restore' AS name, 'admin-brands-restore' AS slug, 'admin.brands.restore' AS description, 'admin.brands' AS module, 'restore' AS action
UNION ALL
SELECT 'admin.brands.status' AS name, 'admin-brands-status' AS slug, 'admin.brands.status' AS description, 'admin.brands' AS module, 'status' AS action
UNION ALL
SELECT 'admin.product-specifications.index' AS name, 'admin-product-specifications-index' AS slug, 'admin.product-specifications.index' AS description, 'admin.product-specifications' AS module, 'index' AS action
UNION ALL
SELECT 'admin.product-specifications.store' AS name, 'admin-product-specifications-store' AS slug, 'admin.product-specifications.store' AS description, 'admin.product-specifications' AS module, 'store' AS action
UNION ALL
SELECT 'admin.product-specifications.update' AS name, 'admin-product-specifications-update' AS slug, 'admin.product-specifications.update' AS description, 'admin.product-specifications' AS module, 'update' AS action
UNION ALL
SELECT 'admin.product-specifications.destroy' AS name, 'admin-product-specifications-destroy' AS slug, 'admin.product-specifications.destroy' AS description, 'admin.product-specifications' AS module, 'destroy' AS action
UNION ALL
SELECT 'admin.product-media.index' AS name, 'admin-product-media-index' AS slug, 'admin.product-media.index' AS description, 'admin.product-media' AS module, 'index' AS action
UNION ALL
SELECT 'admin.variant-media.index' AS name, 'admin-variant-media-index' AS slug, 'admin.variant-media.index' AS description, 'admin.variant-media' AS module, 'index' AS action
UNION ALL
SELECT 'admin.product-media.store' AS name, 'admin-product-media-store' AS slug, 'admin.product-media.store' AS description, 'admin.product-media' AS module, 'store' AS action
UNION ALL
SELECT 'admin.product-media.update' AS name, 'admin-product-media-update' AS slug, 'admin.product-media.update' AS description, 'admin.product-media' AS module, 'update' AS action
UNION ALL
SELECT 'admin.product-media.destroy' AS name, 'admin-product-media-destroy' AS slug, 'admin.product-media.destroy' AS description, 'admin.product-media' AS module, 'destroy' AS action
UNION ALL
SELECT 'admin.inventory.show' AS name, 'admin-inventory-show' AS slug, 'admin.inventory.show' AS description, 'admin.inventory' AS module, 'show' AS action
UNION ALL
SELECT 'admin.inventory.by-variant' AS name, 'admin-inventory-by-variant' AS slug, 'admin.inventory.by-variant' AS description, 'admin.inventory' AS module, 'by-variant' AS action
UNION ALL
SELECT 'admin.inventory.store' AS name, 'admin-inventory-store' AS slug, 'admin.inventory.store' AS description, 'admin.inventory' AS module, 'store' AS action
UNION ALL
SELECT 'admin.inventory.update' AS name, 'admin-inventory-update' AS slug, 'admin.inventory.update' AS description, 'admin.inventory' AS module, 'update' AS action
UNION ALL
SELECT 'admin.inventory-movements.show' AS name, 'admin-inventory-movements-show' AS slug, 'admin.inventory-movements.show' AS description, 'admin.inventory-movements' AS module, 'show' AS action
UNION ALL
SELECT 'admin.inventory-movements.by-variant' AS name, 'admin-inventory-movements-by-variant' AS slug, 'admin.inventory-movements.by-variant' AS description, 'admin.inventory-movements' AS module, 'by-variant' AS action
UNION ALL
SELECT 'admin.inventory-movements.store' AS name, 'admin-inventory-movements-store' AS slug, 'admin.inventory-movements.store' AS description, 'admin.inventory-movements' AS module, 'store' AS action
UNION ALL
SELECT 'admin.inventory-reservations.show' AS name, 'admin-inventory-reservations-show' AS slug, 'admin.inventory-reservations.show' AS description, 'admin.inventory-reservations' AS module, 'show' AS action
UNION ALL
SELECT 'admin.inventory-reservations.by-variant' AS name, 'admin-inventory-reservations-by-variant' AS slug, 'admin.inventory-reservations.by-variant' AS description, 'admin.inventory-reservations' AS module, 'by-variant' AS action
UNION ALL
SELECT 'admin.inventory-reservations.active-by-variant' AS name, 'admin-inventory-reservations-active-by-variant' AS slug, 'admin.inventory-reservations.active-by-variant' AS description, 'admin.inventory-reservations' AS module, 'active-by-variant' AS action
UNION ALL
SELECT 'admin.inventory-reservations.by-cart' AS name, 'admin-inventory-reservations-by-cart' AS slug, 'admin.inventory-reservations.by-cart' AS description, 'admin.inventory-reservations' AS module, 'by-cart' AS action
UNION ALL
SELECT 'admin.inventory-reservations.by-order' AS name, 'admin-inventory-reservations-by-order' AS slug, 'admin.inventory-reservations.by-order' AS description, 'admin.inventory-reservations' AS module, 'by-order' AS action
UNION ALL
SELECT 'admin.inventory-reservations.store' AS name, 'admin-inventory-reservations-store' AS slug, 'admin.inventory-reservations.store' AS description, 'admin.inventory-reservations' AS module, 'store' AS action
UNION ALL
SELECT 'admin.inventory-reservations.status' AS name, 'admin-inventory-reservations-status' AS slug, 'admin.inventory-reservations.status' AS description, 'admin.inventory-reservations' AS module, 'status' AS action
UNION ALL
SELECT 'admin.orders.index' AS name, 'admin-orders-index' AS slug, 'admin.orders.index' AS description, 'admin.orders' AS module, 'index' AS action
UNION ALL
SELECT 'admin.orders.show' AS name, 'admin-orders-show' AS slug, 'admin.orders.show' AS description, 'admin.orders' AS module, 'show' AS action
UNION ALL
SELECT 'admin.orders.status' AS name, 'admin-orders-status' AS slug, 'admin.orders.status' AS description, 'admin.orders' AS module, 'status' AS action
UNION ALL
SELECT 'admin.payments.index' AS name, 'admin-payments-index' AS slug, 'admin.payments.index' AS description, 'admin.payments' AS module, 'index' AS action
UNION ALL
SELECT 'admin.payments.attempts' AS name, 'admin-payments-attempts' AS slug, 'admin.payments.attempts' AS description, 'admin.payments' AS module, 'attempts' AS action
UNION ALL
SELECT 'admin.payments.attempts.show' AS name, 'admin-payments-attempts-show' AS slug, 'admin.payments.attempts.show' AS description, 'admin.payments.attempts' AS module, 'show' AS action
UNION ALL
SELECT 'admin.payments.show' AS name, 'admin-payments-show' AS slug, 'admin.payments.show' AS description, 'admin.payments' AS module, 'show' AS action
UNION ALL
SELECT 'admin.returns.index' AS name, 'admin-returns-index' AS slug, 'admin.returns.index' AS description, 'admin.returns' AS module, 'index' AS action
UNION ALL
SELECT 'admin.returns.show' AS name, 'admin-returns-show' AS slug, 'admin.returns.show' AS description, 'admin.returns' AS module, 'show' AS action
UNION ALL
SELECT 'admin.returns.status' AS name, 'admin-returns-status' AS slug, 'admin.returns.status' AS description, 'admin.returns' AS module, 'status' AS action
UNION ALL
SELECT 'admin.returns.refunds' AS name, 'admin-returns-refunds' AS slug, 'admin.returns.refunds' AS description, 'admin.returns' AS module, 'refunds' AS action
UNION ALL
SELECT 'admin.refunds.store' AS name, 'admin-refunds-store' AS slug, 'admin.refunds.store' AS description, 'admin.refunds' AS module, 'store' AS action
UNION ALL
SELECT 'admin.refunds.status' AS name, 'admin-refunds-status' AS slug, 'admin.refunds.status' AS description, 'admin.refunds' AS module, 'status' AS action
UNION ALL
SELECT 'admin.discovery.tags.index' AS name, 'admin-discovery-tags-index' AS slug, 'admin.discovery.tags.index' AS description, 'admin.discovery.tags' AS module, 'index' AS action
UNION ALL
SELECT 'admin.discovery.tags.store' AS name, 'admin-discovery-tags-store' AS slug, 'admin.discovery.tags.store' AS description, 'admin.discovery.tags' AS module, 'store' AS action
UNION ALL
SELECT 'admin.discovery.tags.update' AS name, 'admin-discovery-tags-update' AS slug, 'admin.discovery.tags.update' AS description, 'admin.discovery.tags' AS module, 'update' AS action
UNION ALL
SELECT 'admin.discovery.tags.destroy' AS name, 'admin-discovery-tags-destroy' AS slug, 'admin.discovery.tags.destroy' AS description, 'admin.discovery.tags' AS module, 'destroy' AS action
UNION ALL
SELECT 'admin.compatibility.index' AS name, 'admin-compatibility-index' AS slug, 'admin.compatibility.index' AS description, 'admin.compatibility' AS module, 'index' AS action
UNION ALL
SELECT 'admin.collections.index' AS name, 'admin-collections-index' AS slug, 'admin.collections.index' AS description, 'admin.collections' AS module, 'index' AS action
UNION ALL
SELECT 'admin.collections.show' AS name, 'admin-collections-show' AS slug, 'admin.collections.show' AS description, 'admin.collections' AS module, 'show' AS action
UNION ALL
SELECT 'admin.collections.store' AS name, 'admin-collections-store' AS slug, 'admin.collections.store' AS description, 'admin.collections' AS module, 'store' AS action
UNION ALL
SELECT 'admin.collections.update' AS name, 'admin-collections-update' AS slug, 'admin.collections.update' AS description, 'admin.collections' AS module, 'update' AS action
UNION ALL
SELECT 'admin.collections.destroy' AS name, 'admin-collections-destroy' AS slug, 'admin.collections.destroy' AS description, 'admin.collections' AS module, 'destroy' AS action
UNION ALL
SELECT 'admin.collections.restore' AS name, 'admin-collections-restore' AS slug, 'admin.collections.restore' AS description, 'admin.collections' AS module, 'restore' AS action
UNION ALL
SELECT 'admin.collections.status' AS name, 'admin-collections-status' AS slug, 'admin.collections.status' AS description, 'admin.collections' AS module, 'status' AS action
UNION ALL
SELECT 'admin.collections.products.attach' AS name, 'admin-collections-products-attach' AS slug, 'admin.collections.products.attach' AS description, 'admin.collections.products' AS module, 'attach' AS action
UNION ALL
SELECT 'admin.collections.products.detach' AS name, 'admin-collections-products-detach' AS slug, 'admin.collections.products.detach' AS description, 'admin.collections.products' AS module, 'detach' AS action
UNION ALL
SELECT 'admin.sliders.index' AS name, 'admin-sliders-index' AS slug, 'admin.sliders.index' AS description, 'admin.sliders' AS module, 'index' AS action
UNION ALL
SELECT 'admin.sliders.show' AS name, 'admin-sliders-show' AS slug, 'admin.sliders.show' AS description, 'admin.sliders' AS module, 'show' AS action
UNION ALL
SELECT 'admin.sliders.store' AS name, 'admin-sliders-store' AS slug, 'admin.sliders.store' AS description, 'admin.sliders' AS module, 'store' AS action
UNION ALL
SELECT 'admin.sliders.update' AS name, 'admin-sliders-update' AS slug, 'admin.sliders.update' AS description, 'admin.sliders' AS module, 'update' AS action
UNION ALL
SELECT 'admin.sliders.destroy' AS name, 'admin-sliders-destroy' AS slug, 'admin.sliders.destroy' AS description, 'admin.sliders' AS module, 'destroy' AS action
UNION ALL
SELECT 'admin.sliders.restore' AS name, 'admin-sliders-restore' AS slug, 'admin.sliders.restore' AS description, 'admin.sliders' AS module, 'restore' AS action
UNION ALL
SELECT 'admin.sliders.status' AS name, 'admin-sliders-status' AS slug, 'admin.sliders.status' AS description, 'admin.sliders' AS module, 'status' AS action
UNION ALL
SELECT 'admin.coupons.index' AS name, 'admin-coupons-index' AS slug, 'admin.coupons.index' AS description, 'admin.coupons' AS module, 'index' AS action
UNION ALL
SELECT 'admin.coupons.show' AS name, 'admin-coupons-show' AS slug, 'admin.coupons.show' AS description, 'admin.coupons' AS module, 'show' AS action
UNION ALL
SELECT 'admin.coupons.store' AS name, 'admin-coupons-store' AS slug, 'admin.coupons.store' AS description, 'admin.coupons' AS module, 'store' AS action
UNION ALL
SELECT 'admin.coupons.update' AS name, 'admin-coupons-update' AS slug, 'admin.coupons.update' AS description, 'admin.coupons' AS module, 'update' AS action
UNION ALL
SELECT 'admin.coupons.destroy' AS name, 'admin-coupons-destroy' AS slug, 'admin.coupons.destroy' AS description, 'admin.coupons' AS module, 'destroy' AS action
UNION ALL
SELECT 'admin.coupons.restore' AS name, 'admin-coupons-restore' AS slug, 'admin.coupons.restore' AS description, 'admin.coupons' AS module, 'restore' AS action
UNION ALL
SELECT 'admin.coupons.status' AS name, 'admin-coupons-status' AS slug, 'admin.coupons.status' AS description, 'admin.coupons' AS module, 'status' AS action
UNION ALL
SELECT 'admin.reviews.index' AS name, 'admin-reviews-index' AS slug, 'admin.reviews.index' AS description, 'admin.reviews' AS module, 'index' AS action
UNION ALL
SELECT 'admin.reviews.show' AS name, 'admin-reviews-show' AS slug, 'admin.reviews.show' AS description, 'admin.reviews' AS module, 'show' AS action
UNION ALL
SELECT 'admin.reviews.status' AS name, 'admin-reviews-status' AS slug, 'admin.reviews.status' AS description, 'admin.reviews' AS module, 'status' AS action
UNION ALL
SELECT 'admin.reviews.note' AS name, 'admin-reviews-note' AS slug, 'admin.reviews.note' AS description, 'admin.reviews' AS module, 'note' AS action
UNION ALL
SELECT 'admin.questions.index' AS name, 'admin-questions-index' AS slug, 'admin.questions.index' AS description, 'admin.questions' AS module, 'index' AS action
UNION ALL
SELECT 'admin.questions.show' AS name, 'admin-questions-show' AS slug, 'admin.questions.show' AS description, 'admin.questions' AS module, 'show' AS action
UNION ALL
SELECT 'admin.questions.status' AS name, 'admin-questions-status' AS slug, 'admin.questions.status' AS description, 'admin.questions' AS module, 'status' AS action
UNION ALL
SELECT 'admin.questions.note' AS name, 'admin-questions-note' AS slug, 'admin.questions.note' AS description, 'admin.questions' AS module, 'note' AS action
UNION ALL
SELECT 'admin.questions.answers.store' AS name, 'admin-questions-answers-store' AS slug, 'admin.questions.answers.store' AS description, 'admin.questions.answers' AS module, 'store' AS action
UNION ALL
SELECT 'admin.answers.status' AS name, 'admin-answers-status' AS slug, 'admin.answers.status' AS description, 'admin.answers' AS module, 'status' AS action
UNION ALL
SELECT 'admin.notifications.create' AS name, 'admin-notifications-create' AS slug, 'admin.notifications.create' AS description, 'admin.notifications' AS module, 'create' AS action
UNION ALL
SELECT 'admin.reports.show' AS name, 'admin-reports-show' AS slug, 'admin.reports.show' AS description, 'admin.reports' AS module, 'show' AS action
UNION ALL
SELECT 'admin.reports.index' AS name, 'admin-reports-index' AS slug, 'admin.reports.index' AS description, 'admin.reports' AS module, 'index' AS action
UNION ALL
SELECT 'admin.reports.sales' AS name, 'admin-reports-sales' AS slug, 'admin.reports.sales' AS description, 'admin.reports' AS module, 'sales' AS action
UNION ALL
SELECT 'admin.reports.orders' AS name, 'admin-reports-orders' AS slug, 'admin.reports.orders' AS description, 'admin.reports' AS module, 'orders' AS action
UNION ALL
SELECT 'admin.reports.products' AS name, 'admin-reports-products' AS slug, 'admin.reports.products' AS description, 'admin.reports' AS module, 'products' AS action
UNION ALL
SELECT 'admin.reports.categories' AS name, 'admin-reports-categories' AS slug, 'admin.reports.categories' AS description, 'admin.reports' AS module, 'categories' AS action
UNION ALL
SELECT 'admin.compatibility.mappings' AS name, 'admin-compatibility-mappings' AS slug, 'admin.compatibility.mappings' AS description, 'admin.compatibility' AS module, 'mappings' AS action
UNION ALL
SELECT 'admin.compatibility.mappings.store' AS name, 'admin-compatibility-mappings-store' AS slug, 'admin.compatibility.mappings.store' AS description, 'admin.compatibility.mappings' AS module, 'store' AS action
UNION ALL
SELECT 'admin.compatibility.mappings.update' AS name, 'admin-compatibility-mappings-update' AS slug, 'admin.compatibility.mappings.update' AS description, 'admin.compatibility.mappings' AS module, 'update' AS action
UNION ALL
SELECT 'admin.compatibility.mappings.destroy' AS name, 'admin-compatibility-mappings-destroy' AS slug, 'admin.compatibility.mappings.destroy' AS description, 'admin.compatibility.mappings' AS module, 'destroy' AS action
UNION ALL
SELECT 'admin.compatibility.devices' AS name, 'admin-compatibility-devices' AS slug, 'admin.compatibility.devices' AS description, 'admin.compatibility' AS module, 'devices' AS action
UNION ALL
SELECT 'admin.compatibility.devices.store' AS name, 'admin-compatibility-devices-store' AS slug, 'admin.compatibility.devices.store' AS description, 'admin.compatibility.devices' AS module, 'store' AS action
UNION ALL
SELECT 'admin.compatibility.devices.update' AS name, 'admin-compatibility-devices-update' AS slug, 'admin.compatibility.devices.update' AS description, 'admin.compatibility.devices' AS module, 'update' AS action
UNION ALL
SELECT 'admin.compatibility.devices.destroy' AS name, 'admin-compatibility-devices-destroy' AS slug, 'admin.compatibility.devices.destroy' AS description, 'admin.compatibility.devices' AS module, 'destroy' AS action
UNION ALL
SELECT 'admin.compatibility.devices.status' AS name, 'admin-compatibility-devices-status' AS slug, 'admin.compatibility.devices.status' AS description, 'admin.compatibility.devices' AS module, 'status' AS action
UNION ALL
SELECT 'admin.audit-logs.index' AS name, 'admin-audit-logs-index' AS slug, 'admin.audit-logs.index' AS description, 'admin.audit-logs' AS module, 'index' AS action
UNION ALL
SELECT 'admin.users.index' AS name, 'admin-users-index' AS slug, 'admin.users.index' AS description, 'admin.users' AS module, 'index' AS action
UNION ALL
SELECT 'admin.users.show' AS name, 'admin-users-show' AS slug, 'admin.users.show' AS description, 'admin.users' AS module, 'show' AS action
UNION ALL
SELECT 'admin.users.status' AS name, 'admin-users-status' AS slug, 'admin.users.status' AS description, 'admin.users' AS module, 'status' AS action
UNION ALL
SELECT 'admin.shipping.methods.index' AS name, 'admin-shipping-methods-index' AS slug, 'admin.shipping.methods.index' AS description, 'admin.shipping.methods' AS module, 'index' AS action
UNION ALL
SELECT 'admin.shipping.methods.store' AS name, 'admin-shipping-methods-store' AS slug, 'admin.shipping.methods.store' AS description, 'admin.shipping.methods' AS module, 'store' AS action
UNION ALL
SELECT 'admin.shipping.methods.update' AS name, 'admin-shipping-methods-update' AS slug, 'admin.shipping.methods.update' AS description, 'admin.shipping.methods' AS module, 'update' AS action
UNION ALL
SELECT 'admin.shipping.methods.destroy' AS name, 'admin-shipping-methods-destroy' AS slug, 'admin.shipping.methods.destroy' AS description, 'admin.shipping.methods' AS module, 'destroy' AS action
UNION ALL
SELECT 'admin.shipping.zones' AS name, 'admin-shipping-zones' AS slug, 'admin.shipping.zones' AS description, 'admin.shipping' AS module, 'zones' AS action
UNION ALL
SELECT 'admin.shipping.zones.show' AS name, 'admin-shipping-zones-show' AS slug, 'admin.shipping.zones.show' AS description, 'admin.shipping.zones' AS module, 'show' AS action
UNION ALL
SELECT 'admin.shipping.zones.status' AS name, 'admin-shipping-zones-status' AS slug, 'admin.shipping.zones.status' AS description, 'admin.shipping.zones' AS module, 'status' AS action
UNION ALL
SELECT 'admin.shipping.shipments' AS name, 'admin-shipping-shipments' AS slug, 'admin.shipping.shipments' AS description, 'admin.shipping' AS module, 'shipments' AS action
UNION ALL
SELECT 'admin.shipping.shipments.show' AS name, 'admin-shipping-shipments-show' AS slug, 'admin.shipping.shipments.show' AS description, 'admin.shipping.shipments' AS module, 'show' AS action
UNION ALL
SELECT 'admin.shipping.shipments.status' AS name, 'admin-shipping-shipments-status' AS slug, 'admin.shipping.shipments.status' AS description, 'admin.shipping.shipments' AS module, 'status' AS action
) AS requested
LEFT JOIN admin_permissions AS existing
    ON existing.name = requested.name
WHERE existing.id IS NULL;

INSERT IGNORE INTO admin_role_permissions (
    role_id,
    permission_id,
    created_at
)
SELECT
    roles.id,
    permissions.id,
    CURRENT_TIMESTAMP
FROM admin_roles AS roles
INNER JOIN admin_permissions AS permissions
    ON permissions.name IN (
        'admin.attributes.index',
        'admin.attributes.show',
        'admin.attributes.store',
        'admin.attributes.update',
        'admin.attributes.destroy',
        'admin.attribute-values.index',
        'admin.attribute-values.show',
        'admin.attribute-values.store',
        'admin.attribute-values.update',
        'admin.attribute-values.destroy',
        'admin.variants.show',
        'admin.products.variants',
        'admin.variants.store',
        'admin.variants.update',
        'admin.variants.destroy',
        'admin.variant-attributes.index',
        'admin.variant-attributes.store',
        'admin.variant-attributes.destroy',
        'admin.attributes.active',
        'admin.attributes.restore',
        'admin.attributes.status',
        'admin.attribute-values.active',
        'admin.attribute-values.restore',
        'admin.attribute-values.status',
        'admin.products.variants.active',
        'admin.products.variant-default',
        'admin.variants.default.set',
        'admin.variants.default.clear',
        'admin.variants.restore',
        'admin.variants.status',
        'admin.products.index',
        'admin.products.show',
        'admin.products.store',
        'admin.products.update',
        'admin.products.destroy',
        'admin.products.active',
        'admin.products.restore',
        'admin.products.status',
        'admin.categories.index',
        'admin.categories.active',
        'admin.categories.show',
        'admin.categories.store',
        'admin.categories.update',
        'admin.categories.destroy',
        'admin.categories.restore',
        'admin.categories.status',
        'admin.brands.index',
        'admin.brands.active',
        'admin.brands.show',
        'admin.brands.store',
        'admin.brands.update',
        'admin.brands.destroy',
        'admin.brands.restore',
        'admin.brands.status',
        'admin.product-specifications.index',
        'admin.product-specifications.store',
        'admin.product-specifications.update',
        'admin.product-specifications.destroy',
        'admin.product-media.index',
        'admin.variant-media.index',
        'admin.product-media.store',
        'admin.product-media.update',
        'admin.product-media.destroy',
        'admin.inventory.show',
        'admin.inventory.by-variant',
        'admin.inventory.store',
        'admin.inventory.update',
        'admin.inventory-movements.show',
        'admin.inventory-movements.by-variant',
        'admin.inventory-movements.store',
        'admin.inventory-reservations.show',
        'admin.inventory-reservations.by-variant',
        'admin.inventory-reservations.active-by-variant',
        'admin.inventory-reservations.by-cart',
        'admin.inventory-reservations.by-order',
        'admin.inventory-reservations.store',
        'admin.inventory-reservations.status',
        'admin.orders.index',
        'admin.orders.show',
        'admin.orders.status',
        'admin.payments.index',
        'admin.payments.attempts',
        'admin.payments.attempts.show',
        'admin.payments.show',
        'admin.returns.index',
        'admin.returns.show',
        'admin.returns.status',
        'admin.returns.refunds',
        'admin.refunds.store',
        'admin.refunds.status',
        'admin.discovery.tags.index',
        'admin.discovery.tags.store',
        'admin.discovery.tags.update',
        'admin.discovery.tags.destroy',
        'admin.compatibility.index',
        'admin.collections.index',
        'admin.collections.show',
        'admin.collections.store',
        'admin.collections.update',
        'admin.collections.destroy',
        'admin.collections.restore',
        'admin.collections.status',
        'admin.collections.products.attach',
        'admin.collections.products.detach',
        'admin.sliders.index',
        'admin.sliders.show',
        'admin.sliders.store',
        'admin.sliders.update',
        'admin.sliders.destroy',
        'admin.sliders.restore',
        'admin.sliders.status',
        'admin.coupons.index',
        'admin.coupons.show',
        'admin.coupons.store',
        'admin.coupons.update',
        'admin.coupons.destroy',
        'admin.coupons.restore',
        'admin.coupons.status',
        'admin.reviews.index',
        'admin.reviews.show',
        'admin.reviews.status',
        'admin.reviews.note',
        'admin.questions.index',
        'admin.questions.show',
        'admin.questions.status',
        'admin.questions.note',
        'admin.questions.answers.store',
        'admin.answers.status',
        'admin.notifications.create',
        'admin.reports.show',
        'admin.reports.index',
        'admin.reports.sales',
        'admin.reports.orders',
        'admin.reports.products',
        'admin.reports.categories',
        'admin.compatibility.mappings',
        'admin.compatibility.mappings.store',
        'admin.compatibility.mappings.update',
        'admin.compatibility.mappings.destroy',
        'admin.compatibility.devices',
        'admin.compatibility.devices.store',
        'admin.compatibility.devices.update',
        'admin.compatibility.devices.destroy',
        'admin.compatibility.devices.status',
        'admin.audit-logs.index',
        'admin.users.index',
        'admin.users.show',
        'admin.users.status',
        'admin.shipping.methods.index',
        'admin.shipping.methods.store',
        'admin.shipping.methods.update',
        'admin.shipping.methods.destroy',
        'admin.shipping.zones',
        'admin.shipping.zones.show',
        'admin.shipping.zones.status',
        'admin.shipping.shipments',
        'admin.shipping.shipments.show',
        'admin.shipping.shipments.status'
    )
WHERE roles.slug = 'super-admin'
  AND roles.status = 'active'
  AND roles.deleted_at IS NULL;

COMMIT;
