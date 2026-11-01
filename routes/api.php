<?php

declare(strict_types=1);

return static function (Router $router): void {

    /*
     * Product Attributes
     */

    $router->get(
        '/admin/attributes',
        [ProductAttributeController::class, 'index'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.attributes.index'
    );

    $router->get(
        '/admin/attributes/{id}',
        [ProductAttributeController::class, 'show'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.attributes.show'
    );

    $router->post(
        '/admin/attributes',
        [ProductAttributeController::class, 'store'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.attributes.store'
    );

    $router->patch(
        '/admin/attributes/{id}',
        [ProductAttributeController::class, 'update'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.attributes.update'
    );

    $router->delete(
        '/admin/attributes/{id}',
        [ProductAttributeController::class, 'destroy'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.attributes.destroy'
    );


    /*
     * Product Attribute Values
     */

    $router->get(
        '/admin/attributes/{attributeId}/values',
        [ProductAttributeValueController::class, 'index'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.attribute-values.index'
    );

    $router->get(
        '/admin/attribute-values/{id}',
        [ProductAttributeValueController::class, 'show'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.attribute-values.show'
    );

    $router->post(
        '/admin/attribute-values',
        [ProductAttributeValueController::class, 'store'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.attribute-values.store'
    );

    $router->patch(
        '/admin/attribute-values/{id}',
        [ProductAttributeValueController::class, 'update'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.attribute-values.update'
    );

    $router->delete(
        '/admin/attribute-values/{id}',
        [ProductAttributeValueController::class, 'destroy'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.attribute-values.destroy'
    );


    /*
     * Product Variants
     */

    $router->get(
        '/admin/variants/{id}',
        [ProductVariantController::class, 'show'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.variants.show'
    );

    $router->get(
        '/admin/products/{productId}/variants',
        [ProductVariantController::class, 'byProduct'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.products.variants'
    );

    $router->post(
        '/admin/variants',
        [ProductVariantController::class, 'store'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.variants.store'
    );

    $router->patch(
        '/admin/variants/{id}',
        [ProductVariantController::class, 'update'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.variants.update'
    );

    $router->delete(
        '/admin/variants/{id}',
        [ProductVariantController::class, 'destroy'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.variants.destroy'
    );


    /*
     * Product Variant Attributes
     */

    $router->get(
        '/admin/variants/{variantId}/attributes',
        [ProductVariantAttributeController::class, 'index'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.variant-attributes.index'
    );

    $router->post(
        '/admin/variant-attributes',
        [ProductVariantAttributeController::class, 'store'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.variant-attributes.store'
    );

    $router->delete(
        '/admin/variant-attributes',
        [ProductVariantAttributeController::class, 'destroy'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.variant-attributes.destroy'
    );

    /*
 * Product Attribute lifecycle
 */

    $router->get(
        '/admin/attributes/active',
        [ProductAttributeController::class, 'active'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.attributes.active'
    );

    $router->post(
        '/admin/attributes/{id}/restore',
        [ProductAttributeController::class, 'restore'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.attributes.restore'
    );

    $router->patch(
        '/admin/attributes/{id}/status',
        [ProductAttributeController::class, 'status'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.attributes.status'
    );


    /*
 * Product Attribute Value lifecycle
 */

    $router->get(
        '/admin/attributes/{attributeId}/values/active',
        [ProductAttributeValueController::class, 'active'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.attribute-values.active'
    );

    $router->post(
        '/admin/attribute-values/{id}/restore',
        [ProductAttributeValueController::class, 'restore'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.attribute-values.restore'
    );

    $router->patch(
        '/admin/attribute-values/{id}/status',
        [ProductAttributeValueController::class, 'status'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.attribute-values.status'
    );


    /*
 * Product Variant lifecycle
 */

    $router->get(
        '/admin/products/{productId}/variants/active',
        [ProductVariantController::class, 'activeByProduct'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.products.variants.active'
    );

    $router->get(
        '/admin/products/{productId}/variant-default',
        [ProductVariantController::class, 'defaultByProduct'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.products.variant-default'
    );

    $router->post(
        '/admin/variants/{id}/default',
        [ProductVariantController::class, 'setDefault'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.variants.default.set'
    );

    $router->delete(
        '/admin/variants/{id}/default',
        [ProductVariantController::class, 'clearDefault'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.variants.default.clear'
    );

    $router->post(
        '/admin/variants/{id}/restore',
        [ProductVariantController::class, 'restore'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.variants.restore'
    );

    $router->patch(
        '/admin/variants/{id}/status',
        [ProductVariantController::class, 'status'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.variants.status'
    );

    /*
 * Product API
 */

    /* 09.63 */
    $router->get(
        '/admin/products',
        [ProductController::class, 'index'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.products.index'
    );

    /* 09.64 */
    $router->get(
        '/admin/products/{id}',
        [ProductController::class, 'show'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.products.show'
    );

    /* 09.65 */
    $router->post(
        '/admin/products',
        [ProductController::class, 'store'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.products.store'
    );

    /* 09.66 */
    $router->put(
        '/admin/products/{id}',
        [ProductController::class, 'update'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.products.update'
    );

    /* 09.67 */
    $router->delete(
        '/admin/products/{id}',
        [ProductController::class, 'destroy'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.products.destroy'
    );

    /* 09.68 */
    $router->get(
        '/admin/products/active',
        [ProductController::class, 'active'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.products.active'
    );

    $router->post(
        '/admin/products/{id}/restore',
        [ProductController::class, 'restore'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.products.restore'
    );

    /* 09.69 */
    $router->patch(
        '/admin/products/{id}/status',
        [ProductController::class, 'status'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.products.status'
    );

    /*
 * Category API
 *
 * IMPORTANT:
 * /active must stay before /{id}.
 */

    /* 09.75 */
    $router->get(
        '/admin/categories',
        [CategoryController::class, 'index'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.categories.index'
    );

    $router->get(
        '/admin/categories/active',
        [CategoryController::class, 'active'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.categories.active'
    );

    $router->get(
        '/admin/categories/{id}',
        [CategoryController::class, 'show'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.categories.show'
    );

    $router->post(
        '/admin/categories',
        [CategoryController::class, 'store'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.categories.store'
    );

    $router->put(
        '/admin/categories/{id}',
        [CategoryController::class, 'update'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.categories.update'
    );

    $router->delete(
        '/admin/categories/{id}',
        [CategoryController::class, 'destroy'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.categories.destroy'
    );

    $router->post(
        '/admin/categories/{id}/restore',
        [CategoryController::class, 'restore'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.categories.restore'
    );

    $router->patch(
        '/admin/categories/{id}/status',
        [CategoryController::class, 'status'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.categories.status'
    );

    /*
 * Brand API
 *
 * IMPORTANT:
 * /active must stay before /{id}.
 */

    /* 09.79 */
    $router->get(
        '/admin/brands',
        [BrandController::class, 'index'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.brands.index'
    );

    $router->get(
        '/admin/brands/active',
        [BrandController::class, 'active'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.brands.active'
    );

    $router->get(
        '/admin/brands/{id}',
        [BrandController::class, 'show'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.brands.show'
    );

    $router->post(
        '/admin/brands',
        [BrandController::class, 'store'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.brands.store'
    );

    $router->put(
        '/admin/brands/{id}',
        [BrandController::class, 'update'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.brands.update'
    );

    $router->delete(
        '/admin/brands/{id}',
        [BrandController::class, 'destroy'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.brands.destroy'
    );

    $router->post(
        '/admin/brands/{id}/restore',
        [BrandController::class, 'restore'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.brands.restore'
    );

    $router->patch(
        '/admin/brands/{id}/status',
        [BrandController::class, 'status'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.brands.status'
    );

    /*
 * Product Variant Attribute API
 */

    /* 09.88 - list */
    $router->get(
        '/admin/variants/{variantId}/attributes',
        [ProductVariantAttributeController::class, 'index'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.variant-attributes.index'
    );

    /* 09.88 - create */
    $router->post(
        '/admin/variant-attributes',
        [ProductVariantAttributeController::class, 'store'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.variant-attributes.store'
    );

    /* 09.88 - delete */
    $router->delete(
        '/admin/variant-attributes',
        [ProductVariantAttributeController::class, 'destroy'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.variant-attributes.destroy'
    );

    /*
 * Product Specification API
 */

    /* 10.19 - list */
    $router->get(
        '/admin/products/{productId}/specifications',
        [ProductSpecificationController::class, 'index'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.product-specifications.index'
    );

    /* 10.19 - create */
    $router->post(
        '/admin/product-specifications',
        [ProductSpecificationController::class, 'store'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.product-specifications.store'
    );

    /* 10.19 - update */
    $router->put(
        '/admin/product-specifications',
        [ProductSpecificationController::class, 'update'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.product-specifications.update'
    );

    /* 10.19 - delete */
    $router->delete(
        '/admin/product-specifications',
        [ProductSpecificationController::class, 'destroy'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.product-specifications.destroy'
    );

    /*
 * Product Media API
 */

    /* 10.29 - Product media list */
    $router->get(
        '/admin/products/{productId}/media',
        [ProductMediaController::class, 'index'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.product-media.index'
    );

    /* 10.29 - Variant media list */
    $router->get(
        '/admin/variants/{variantId}/media',
        [ProductMediaController::class, 'indexByVariant'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.variant-media.index'
    );

    /* 10.29 - Create */
    $router->post(
        '/admin/product-media',
        [ProductMediaController::class, 'store'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.product-media.store'
    );

    /* 10.29 - Update */
    $router->put(
        '/admin/product-media/{id}',
        [ProductMediaController::class, 'update'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.product-media.update'
    );

    /* 10.29 - Delete */
    $router->delete(
        '/admin/product-media/{id}',
        [ProductMediaController::class, 'destroy'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.product-media.destroy'
    );

    /*
 * Inventory
 */

    $router->get(
        '/admin/inventory/{id}',
        [InventoryController::class, 'show'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.inventory.show'
    );

    $router->get(
        '/admin/variants/{variantId}/inventory',
        [InventoryController::class, 'byVariant'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.inventory.by-variant'
    );

    $router->post(
        '/admin/inventory',
        [InventoryController::class, 'store'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.inventory.store'
    );

    $router->patch(
        '/admin/inventory/{id}',
        [InventoryController::class, 'update'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.inventory.update'
    );

    /*
 * Inventory Movements
 */

    $router->get(
        '/admin/inventory-movements/{id}',
        [InventoryMovementController::class, 'show'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.inventory-movements.show'
    );

    $router->get(
        '/admin/variants/{variantId}/inventory-movements',
        [InventoryMovementController::class, 'byVariant'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.inventory-movements.by-variant'
    );

    $router->post(
        '/admin/inventory-movements',
        [InventoryMovementController::class, 'store'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.inventory-movements.store'
    );


    /*
 * Inventory Reservations
 */

    $router->get(
        '/admin/inventory-reservations/{id}',
        [InventoryReservationController::class, 'show'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.inventory-reservations.show'
    );

    $router->get(
        '/admin/variants/{variantId}/inventory-reservations',
        [InventoryReservationController::class, 'byVariant'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.inventory-reservations.by-variant'
    );

    $router->get(
        '/admin/variants/{variantId}/inventory-reservations/active',
        [InventoryReservationController::class, 'activeByVariant'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.inventory-reservations.active-by-variant'
    );

    $router->get(
        '/admin/carts/{cartId}/inventory-reservations',
        [InventoryReservationController::class, 'byCart'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.inventory-reservations.by-cart'
    );

    $router->get(
        '/admin/orders/{orderId}/inventory-reservations',
        [InventoryReservationController::class, 'byOrder'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.inventory-reservations.by-order'
    );

    $router->post(
        '/admin/inventory-reservations',
        [InventoryReservationController::class, 'store'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.inventory-reservations.store'
    );

    $router->patch(
        '/admin/inventory-reservations/{id}/status',
        [InventoryReservationController::class, 'status'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.inventory-reservations.status'
    );

    /*
|--------------------------------------------------------------------------
| Wishlist
|--------------------------------------------------------------------------
*/

    $router->get(
        '/wishlists',
        [WishlistController::class, 'index'],
        [
            AuthMiddleware::class,
        ],
        'wishlists.index'
    );

    $router->post(
        '/wishlists',
        [WishlistController::class, 'store'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'wishlists.store'
    );

    $router->put(
        '/wishlists/{id}',
        [WishlistController::class, 'update'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'wishlists.update'
    );

    $router->post(
        '/wishlists/{id}/default',
        [WishlistController::class, 'setDefault'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'wishlists.default'
    );

    $router->delete(
        '/wishlists/{id}',
        [WishlistController::class, 'destroy'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'wishlists.destroy'
    );

    /*
|--------------------------------------------------------------------------
| Wishlist Items
|--------------------------------------------------------------------------
*/

    $router->get(
        '/wishlists/{wishlistId}/items',
        [WishlistController::class, 'items'],
        [
            AuthMiddleware::class,
        ],
        'wishlists.items'
    );

    $router->post(
        '/wishlist-items',
        [WishlistController::class, 'addItem'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'wishlist-items.store'
    );

    $router->delete(
        '/wishlist-items/{id}',
        [WishlistController::class, 'removeItem'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'wishlist-items.destroy'
    );

    $router->get(
        '/checkout/shipping-methods',
        [ShippingController::class, 'checkoutMethods'],
        [
            AuthMiddleware::class,
        ],
        'checkout.shipping-methods'
    );

    /*
    |--------------------------------------------------------------------------
    | Cart
    |--------------------------------------------------------------------------
    */

    $router->get(
        '/cart',
        [CartController::class, 'index'],
        [
            AuthMiddleware::class,
        ],
        'cart.index'
    );

    $router->get(
        '/cart/{id}',
        [CartController::class, 'show'],
        [
            AuthMiddleware::class,
        ],
        'cart.show'
    );

    $router->post(
        '/cart',
        [CartController::class, 'store'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'cart.store'
    );

    $router->put(
        '/cart/{id}',
        [CartController::class, 'update'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'cart.update'
    );

    $router->post(
        '/cart/{id}/status',
        [CartController::class, 'updateStatus'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'cart.status'
    );

    $router->get(
        '/cart/{cartId}/items',
        [CartController::class, 'items'],
        [
            AuthMiddleware::class,
        ],
        'cart.items'
    );

    $router->post(
        '/cart-items',
        [CartController::class, 'addItem'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'cart-items.store'
    );

    $router->put(
        '/cart-items/{id}',
        [CartController::class, 'updateItem'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'cart-items.update'
    );

    $router->delete(
        '/cart-items/{id}',
        [CartController::class, 'removeItem'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'cart-items.destroy'
    );

    /*
|--------------------------------------------------------------------------
| Checkout
|--------------------------------------------------------------------------
*/

    $router->get(
        '/checkout',
        [CheckoutController::class, 'review'],
        [
            AuthMiddleware::class,
        ],
        'checkout.review'
    );

    /*
 * 14.48 — Customer Orders API
 */

    $router->post(
        '/orders',
        [OrderController::class, 'store'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'orders.store'
    );

    $router->get(
        '/orders',
        [OrderController::class, 'index'],
        [
            AuthMiddleware::class,
        ],
        'orders.index'
    );

    $router->get(
        '/orders/{id}',
        [OrderController::class, 'show'],
        [
            AuthMiddleware::class,
        ],
        'orders.show'
    );

    /*
 * 14.68–14.70 — Admin Orders API
 */

    $router->get(
        '/admin/orders',
        [OrderController::class, 'adminIndex'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.orders.index'
    );

    $router->get(
        '/admin/orders/{id}',
        [OrderController::class, 'adminShow'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.orders.show'
    );

    $router->patch(
        '/admin/orders/{id}/status',
        [OrderController::class, 'adminStatus'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.orders.status'
    );

    /*
 * 16.41–16.42 — Shipping API
 */

    $router->get(
        '/shipments/{id}',
        [ShippingController::class, 'show'],
        [
            AuthMiddleware::class,
        ],
        'shipments.show'
    );

    $router->get(
        '/orders/{orderId}/shipments',
        [ShippingController::class, 'byOrder'],
        [
            AuthMiddleware::class,
        ],
        'shipments.by-order'
    );

    /*
     * 15.139–15.140 — Customer Payment Lifecycle
     */

    $router->post(
        '/payments/attempts',
        [PaymentController::class, 'start'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'payments.attempts.start'
    );

    $router->post(
        '/payments/attempts/{id}/complete',
        [PaymentController::class, 'complete'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'payments.attempts.complete'
    );

    $router->post(
        '/payments/attempts/{id}/fail',
        [PaymentController::class, 'fail'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'payments.attempts.fail'
    );

    $router->get(
        '/admin/payments',
        [PaymentAdminController::class, 'index'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.payments.index'
    );

    $router->get(
        '/admin/payments/attempts',
        [PaymentAdminController::class, 'attempts'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.payments.attempts'
    );

    $router->get(
        '/admin/payments/attempts/{id}',
        [PaymentAdminController::class, 'attemptShow'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.payments.attempts.show'
    );

    $router->get(
        '/admin/payments/{id}',
        [PaymentAdminController::class, 'show'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.payments.show'
    );

    /*
     * 17.41–17.45 — Returns + Refunds API
     */

    $router->get(
        '/returns',
        [ReturnController::class, 'index'],
        [
            AuthMiddleware::class,
        ],
        'returns.index'
    );

    $router->get(
        '/returns/{id}',
        [ReturnController::class, 'show'],
        [
            AuthMiddleware::class,
        ],
        'returns.show'
    );

    $router->post(
        '/returns',
        [ReturnController::class, 'store'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'returns.store'
    );

    $router->get(
        '/admin/returns',
        [ReturnController::class, 'adminIndex'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.returns.index'
    );

    $router->get(
        '/admin/returns/{id}',
        [ReturnController::class, 'adminShow'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.returns.show'
    );

    $router->patch(
        '/admin/returns/{id}/status',
        [ReturnController::class, 'adminStatus'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.returns.status'
    );

    $router->get(
        '/admin/returns/{id}/refunds',
        [ReturnController::class, 'adminRefunds'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.returns.refunds'
    );

    $router->post(
        '/admin/refunds',
        [ReturnController::class, 'adminCreateRefund'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.refunds.store'
    );

    $router->patch(
        '/admin/refunds/{id}/status',
        [ReturnController::class, 'adminRefundStatus'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.refunds.status'
    );

    $router->get(
        '/discovery/tags',
        [DiscoveryController::class, 'tags'],
        [],
        'discovery.tags'
    );

    $router->get(
        '/discovery/tags/{tagId}/products',
        [DiscoveryController::class, 'productsByTag'],
        [],
        'discovery.products-by-tag'
    );

    $router->get(
        '/discovery/{slug}/products',
        [DiscoveryController::class, 'productsBySlug'],
        [],
        'discovery.products-by-slug'
    );

    $router->get(
        '/admin/discovery/tags',
        [DiscoveryController::class, 'adminTags'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.discovery.tags.index'
    );

    $router->post(
        '/admin/discovery/tags',
        [DiscoveryController::class, 'storeTag'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.discovery.tags.store'
    );

    $router->patch(
        '/admin/discovery/tags/{id}',
        [DiscoveryController::class, 'updateTag'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.discovery.tags.update'
    );

    $router->delete(
        '/admin/discovery/tags/{id}',
        [DiscoveryController::class, 'destroyTag'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.discovery.tags.destroy'
    );

    /*
 * 18.41–18.49 — Compatibility + Setup Integration
 */

    $router->get(
        '/compatibility/devices',
        [CompatibilityController::class, 'devices'],
        [
            AuthMiddleware::class,
        ],
        'compatibility.devices'
    );

    $router->get(
        '/compatibility/devices/{deviceId}/products',
        [CompatibilityController::class, 'products'],
        [
            AuthMiddleware::class,
        ],
        'compatibility.products'
    );

    $router->get(
        '/admin/compatibility',
        [CompatibilityController::class, 'adminIndex'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.compatibility.index'
    );

    $router->get(
        '/setup/devices',
        [SetupController::class, 'devices'],
        [
            AuthMiddleware::class,
        ],
        'setup.devices'
    );

    $router->get(
        '/setup/devices/{deviceId}/accessories',
        [SetupController::class, 'accessories'],
        [
            AuthMiddleware::class,
        ],
        'setup.accessories'
    );

    $router->get(
        '/setup/devices/{deviceId}/products',
        [SetupController::class, 'products'],
        [
            AuthMiddleware::class,
        ],
        'setup.products'
    );

    /*
 * 19.21–19.30 — Collection Customer API
 */

    $router->get(
        '/collections',
        [CollectionController::class, 'index'],
        [
            AuthMiddleware::class,
        ],
        'collections.index'
    );

    $router->get(
        '/collections/{id}',
        [CollectionController::class, 'show'],
        [
            AuthMiddleware::class,
        ],
        'collections.show'
    );

    $router->get(
        '/collections/slug/{slug}',
        [CollectionController::class, 'showBySlug'],
        [
            AuthMiddleware::class,
        ],
        'collections.slug'
    );

    /*
 * 19.31–19.40 — Admin Collections
 */

    $router->get(
        '/admin/collections',
        [CollectionController::class, 'adminIndex'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.collections.index'
    );

    $router->get(
        '/admin/collections/{id}',
        [CollectionController::class, 'adminShow'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.collections.show'
    );

    $router->post(
        '/admin/collections',
        [CollectionController::class, 'store'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.collections.store'
    );

    $router->put(
        '/admin/collections/{id}',
        [CollectionController::class, 'update'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.collections.update'
    );

    $router->delete(
        '/admin/collections/{id}',
        [CollectionController::class, 'destroy'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.collections.destroy'
    );

    $router->post(
        '/admin/collections/{id}/restore',
        [CollectionController::class, 'restore'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.collections.restore'
    );

    $router->patch(
        '/admin/collections/{id}/status',
        [CollectionController::class, 'status'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.collections.status'
    );

    $router->post(
        '/admin/collections/{id}/products',
        [CollectionController::class, 'attachProduct'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.collections.products.attach'
    );

    $router->delete(
        '/admin/collections/{id}/products/{productId}',
        [CollectionController::class, 'detachProduct'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.collections.products.detach'
    );

    /*
 * 19.81–19.90 — Slider Customer + Admin API
 */

    $router->get(
        '/sliders',
        [SliderController::class, 'index'],
        [
            AuthMiddleware::class,
        ],
        'sliders.index'
    );

    $router->get(
        '/sliders/{id}',
        [SliderController::class, 'show'],
        [
            AuthMiddleware::class,
        ],
        'sliders.show'
    );

    $router->get(
        '/sliders/slug/{slug}',
        [SliderController::class, 'showBySlug'],
        [
            AuthMiddleware::class,
        ],
        'sliders.slug'
    );

    $router->get(
        '/admin/sliders',
        [SliderController::class, 'adminIndex'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.sliders.index'
    );

    $router->get(
        '/admin/sliders/{id}',
        [SliderController::class, 'adminShow'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.sliders.show'
    );

    $router->post(
        '/admin/sliders',
        [SliderController::class, 'store'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.sliders.store'
    );

    $router->put(
        '/admin/sliders/{id}',
        [SliderController::class, 'update'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.sliders.update'
    );

    $router->delete(
        '/admin/sliders/{id}',
        [SliderController::class, 'destroy'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.sliders.destroy'
    );

    $router->post(
        '/admin/sliders/{id}/restore',
        [SliderController::class, 'restore'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.sliders.restore'
    );

    $router->patch(
        '/admin/sliders/{id}/status',
        [SliderController::class, 'status'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.sliders.status'
    );

    /*
 * Coupon API
 */

    /* 19.149 - list */
    $router->get(
        '/admin/coupons',
        [CouponController::class, 'index'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.coupons.index'
    );

    /* 19.149 - show */
    $router->get(
        '/admin/coupons/{id}',
        [CouponController::class, 'show'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.coupons.show'
    );

    /* 19.149 - create */
    $router->post(
        '/admin/coupons',
        [CouponController::class, 'store'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.coupons.store'
    );

    /* 19.149 - update */
    $router->put(
        '/admin/coupons/{id}',
        [CouponController::class, 'update'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.coupons.update'
    );

    /* 19.149 - delete */
    $router->delete(
        '/admin/coupons/{id}',
        [CouponController::class, 'destroy'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.coupons.destroy'
    );

    /* 19.149 - restore */
    $router->post(
        '/admin/coupons/{id}/restore',
        [CouponController::class, 'restore'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.coupons.restore'
    );

    /* 19.149 - status */
    $router->patch(
        '/admin/coupons/{id}/status',
        [CouponController::class, 'status'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.coupons.status'
    );

    $router->get(
        '/admin/reviews',
        [ReviewController::class, 'adminIndex'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.reviews.index'
    );

    $router->get(
        '/admin/reviews/{id}',
        [ReviewController::class, 'adminShow'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.reviews.show'
    );

    $router->patch(
        '/admin/reviews/{id}/status',
        [ReviewController::class, 'adminStatus'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.reviews.status'
    );

    $router->patch(
        '/admin/reviews/{id}/note',
        [ReviewController::class, 'adminNote'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.reviews.note'
    );

    /*
     * 20.101–20.110 — Product Questions & Answers API + Admin
     */

    $router->get(
        '/products/{productId}/questions',
        [ProductQuestionController::class, 'index'],
        [],
        'products.questions.index'
    );

    $router->get(
        '/questions/{id}',
        [ProductQuestionController::class, 'show'],
        [
            AuthMiddleware::class,
        ],
        'questions.show'
    );

    $router->post(
        '/products/{productId}/questions',
        [ProductQuestionController::class, 'store'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'products.questions.store'
    );

    $router->get(
        '/questions/{questionId}/answers',
        [ProductAnswerController::class, 'index'],
        [],
        'questions.answers.index'
    );

    $router->get(
        '/answers/{id}',
        [ProductAnswerController::class, 'show'],
        [
            AuthMiddleware::class,
        ],
        'answers.show'
    );

    $router->get(
        '/admin/questions',
        [ProductQuestionController::class, 'adminIndex'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.questions.index'
    );

    $router->get(
        '/admin/questions/{id}',
        [ProductQuestionController::class, 'adminShow'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.questions.show'
    );

    $router->patch(
        '/admin/questions/{id}/status',
        [ProductQuestionController::class, 'adminStatus'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.questions.status'
    );

    $router->patch(
        '/admin/questions/{id}/note',
        [ProductQuestionController::class, 'adminNote'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.questions.note'
    );

    $router->post(
        '/admin/questions/{questionId}/answers',
        [ProductAnswerController::class, 'adminStore'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.questions.answers.store'
    );

    $router->patch(
        '/admin/answers/{id}/status',
        [ProductAnswerController::class, 'adminStatus'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.answers.status'
    );

    /*
     * 20.131–20.140 — Notification API
     */

    $router->get(
        '/notifications',
        [NotificationController::class, 'index'],
        [
            AuthMiddleware::class,
        ],
        'notifications.index'
    );

    $router->get(
        '/notifications/unread',
        [NotificationController::class, 'unread'],
        [
            AuthMiddleware::class,
        ],
        'notifications.unread'
    );

    $router->get(
        '/notifications/count',
        [NotificationController::class, 'count'],
        [
            AuthMiddleware::class,
        ],
        'notifications.count'
    );

    $router->get(
        '/notifications/{id}',
        [NotificationController::class, 'show'],
        [
            AuthMiddleware::class,
        ],
        'notifications.show'
    );

    $router->patch(
        '/notifications/{id}/read',
        [NotificationController::class, 'markAsRead'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'notifications.read'
    );

    $router->patch(
        '/notifications/read-all',
        [NotificationController::class, 'markAllAsRead'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'notifications.read-all'
    );

    /*
     * 20.151–20.160 — Notification Admin Integration
     */

    $router->post(
        '/admin/notifications',
        [NotificationController::class, 'adminCreate'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.notifications.create'
    );

    $router->get(
        '/search',
        [SearchController::class, 'index'],
        [],
        'search.index'
    );

    $router->get(
        '/search/suggestions',
        [SearchController::class, 'suggest'],
        [],
        'search.suggestions'
    );

    $router->get(
        '/search/filters',
        [SearchController::class, 'filters'],
        [],
        'search.filters'
    );

    $router->get(
        '/admin/reports/{report}',
        [ReportController::class, 'show'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.reports.show'
    );

    $router->get(
        '/admin/reports',
        [AdminController::class, 'reports'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.reports.index'
    );

    $router->get(
        '/admin/reports/sales',
        [AdminController::class, 'salesReports'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.reports.sales'
    );

    $router->get(
        '/admin/reports/orders',
        [AdminController::class, 'orderReports'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.reports.orders'
    );

    $router->get(
        '/admin/reports/products',
        [AdminController::class, 'productReports'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.reports.products'
    );

    $router->get(
        '/admin/reports/categories',
        [AdminController::class, 'categoryReports'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.reports.categories'
    );

    /*
 * Phase 22 Batch 20 — Compatibility Admin
 */

    /*
 * Phase 12 — Compatibility Mapping Admin
 */

    $router->get(
        '/admin/compatibility/mappings',
        [CompatibilityController::class, 'adminMappings'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
        ],
        'admin.compatibility.mappings'
    );

    $router->post(
        '/admin/compatibility/mappings',
        [CompatibilityController::class, 'adminStoreMapping'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.compatibility.mappings.store'
    );

    $router->put(
        '/admin/compatibility/mappings',
        [CompatibilityController::class, 'adminUpdateMapping'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.compatibility.mappings.update'
    );

    $router->delete(
        '/admin/compatibility/mappings',
        [CompatibilityController::class, 'adminDeleteMapping'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.compatibility.mappings.destroy'
    );

    $router->get(
        '/admin/compatibility/devices',
        [CompatibilityController::class, 'adminDevices'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.compatibility.devices'
    );

    $router->post(
        '/admin/compatibility/devices',
        [CompatibilityController::class, 'adminStoreDevice'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.compatibility.devices.store'
    );

    $router->put(
        '/admin/compatibility/devices/{id}',
        [CompatibilityController::class, 'adminUpdateDevice'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.compatibility.devices.update'
    );

    $router->delete(
        '/admin/compatibility/devices/{id}',
        [CompatibilityController::class, 'adminDeleteDevice'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.compatibility.devices.destroy'
    );

    $router->patch(
        '/admin/compatibility/devices/{id}/status',
        [CompatibilityController::class, 'adminStatusDevice'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.compatibility.devices.status'
    );


    /*
 * Phase 22 Batch 20 — Audit Logs
 */

    $router->get(
        '/admin/audit-logs',
        [AuditLogController::class, 'index'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.audit-logs.index'
    );


    /*
 * Phase 22 Batch 20 — Users
 */

    $router->get(
        '/admin/users',
        [UserAdminController::class, 'index'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.users.index'
    );

    $router->get(
        '/admin/users/{id}',
        [UserAdminController::class, 'show'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.users.show'
    );

    $router->patch(
        '/admin/users/{id}/status',
        [UserAdminController::class, 'status'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.users.status'
    );


    /*
 * Phase 22 Batch 20 — Shipping Methods
 */

    $router->get(
        '/admin/shipping/methods',
        [ShippingAdminController::class, 'index'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.shipping.methods.index'
    );

    $router->post(
        '/admin/shipping/methods',
        [ShippingAdminController::class, 'store'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.shipping.methods.store'
    );

    $router->put(
        '/admin/shipping/methods/{id}',
        [ShippingAdminController::class, 'update'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.shipping.methods.update'
    );

    $router->delete(
        '/admin/shipping/methods/{id}',
        [ShippingAdminController::class, 'destroy'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.shipping.methods.destroy'
    );

    $router->get(
        '/admin/shipping/zones',
        [ShippingAdminController::class, 'zones'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.shipping.zones'
    );

    $router->get(
        '/admin/shipping/zones/{id}',
        [ShippingAdminController::class, 'zoneShow'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.shipping.zones.show'
    );

    $router->patch(
        '/admin/shipping/zones/{id}/status',
        [ShippingAdminController::class, 'zoneStatus'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.shipping.zones.status'
    );

    $router->get(
        '/admin/shipping/shipments',
        [ShippingAdminController::class, 'shipments'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.shipping.shipments'
    );

    $router->get(
        '/admin/shipping/shipments/{id}',
        [ShippingAdminController::class, 'shipmentShow'],
        [AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class],
        'admin.shipping.shipments.show'
    );

    $router->patch(
        '/admin/shipping/shipments/{id}/status',
        [ShippingAdminController::class, 'shipmentStatus'],
        [
            AdminAuthMiddleware::class,
            AdminRoutePermissionMiddleware::class,
            CsrfMiddleware::class,
        ],
        'admin.shipping.shipments.status'
    );

    /*
     * Phase 24 — Storefront Public API
     */



    $router->get(
        '/storefront/categories',
        [StorefrontController::class, 'categories'],
        [],
        'storefront.categories'
    );

    $router->get(
        '/storefront/brands',
        [StorefrontController::class, 'brands'],
        [],
        'storefront.brands'
    );

    $router->get(
        '/auth/status',
        [AuthController::class, 'status'],
        [],
        'auth.status'
    );

    $router->get(
        '/auth/csrf',
        [AuthController::class, 'csrf'],
        [],
        'auth.csrf'
    );

    $router->post(
        '/login',
        [AuthController::class, 'login'],
        [
            CsrfMiddleware::class,
        ],
        'auth.login'
    );

    $router->post(
        '/register',
        [AuthController::class, 'register'],
        [
            CsrfMiddleware::class,
        ],
        'auth.register'
    );

    $router->post(
        '/logout',
        [AuthController::class, 'logout'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'auth.logout'
    );

    $router->post(
        '/forgot-password',
        [AuthController::class, 'forgotPassword'],
        [
            CsrfMiddleware::class,
        ],
        'auth.forgot-password'
    );

    $router->post(
        '/reset-password',
        [AuthController::class, 'resetPassword'],
        [
            CsrfMiddleware::class,
        ],
        'auth.reset-password'
    );

    $router->post(
        '/verify-email',
        [AuthController::class, 'verifyEmail'],
        [
            CsrfMiddleware::class,
        ],
        'auth.verify-email'
    );

    $router->post(
        '/resend-email-verification',
        [AuthController::class, 'resendEmailVerification'],
        [
            CsrfMiddleware::class,
        ],
        'auth.resend-email-verification'
    );

    /*
     * Phase 24 — Customer Account API
     */

    $router->get(
        '/account/security',
        [AuthController::class, 'securitySummary'],
        [AuthMiddleware::class],
        'account.security'
    );

    $router->get(
        '/account/verification-status',
        [AuthController::class, 'verificationStatus'],
        [AuthMiddleware::class],
        'account.verification-status'
    );

    $router->get(
        '/account/sessions',
        [AuthController::class, 'sessions'],
        [AuthMiddleware::class],
        'account.sessions'
    );

    $router->post(
        '/account/sessions/revoke',
        [AuthController::class, 'revokeSession'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'account.sessions.revoke'
    );

    $router->post(
        '/logout-other-sessions',
        [AuthController::class, 'logoutOtherSessions'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'auth.logout-other-sessions'
    );

    $router->post(
        '/account/password',
        [AuthController::class, 'changePassword'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'account.password'
    );

    $router->get(
        '/account/profile',
        [AuthController::class, 'profile'],
        [AuthMiddleware::class],
        'account.profile'
    );

    $router->patch(
        '/account/profile',
        [AuthController::class, 'updateProfile'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'account.profile.update'
    );

    $router->get(
        '/account/addresses',
        [UserAddressController::class, 'index'],
        [AuthMiddleware::class],
        'account.addresses'
    );

    $router->post(
        '/account/addresses',
        [UserAddressController::class, 'store'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'account.addresses.store'
    );

    $router->put(
        '/account/addresses/{id}',
        [UserAddressController::class, 'update'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'account.addresses.update'
    );

    $router->delete(
        '/account/addresses/{id}',
        [UserAddressController::class, 'destroy'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'account.addresses.destroy'
    );

    $router->post(
        '/account/addresses/{id}/default',
        [UserAddressController::class, 'setDefault'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'account.addresses.default'
    );



    $router->get(
        '/storefront/categories',
        [StorefrontController::class, 'categories'],
        [],
        'storefront.categories'
    );

    $router->get(
        '/storefront/brands',
        [StorefrontController::class, 'brands'],
        [],
        'storefront.brands'
    );

    /*
 * Public Storefront Catalog
 */

    $router->get(
        '/storefront/products',
        [StorefrontController::class, 'products'],
        [],
        'storefront.products'
    );

    $router->get(
        '/storefront/products/detail',
        [StorefrontController::class, 'product'],
        [],
        'storefront.product'
    );

    $router->get(
        '/storefront/categories',
        [StorefrontController::class, 'categories'],
        [],
        'storefront.categories'
    );

    $router->get(
        '/storefront/categories/detail',
        [StorefrontController::class, 'category'],
        [],
        'storefront.category'
    );

    $router->get(
        '/storefront/brands',
        [StorefrontController::class, 'brands'],
        [],
        'storefront.brands'
    );

    $router->get(
        '/storefront/brands/detail',
        [StorefrontController::class, 'brand'],
        [],
        'storefront.brand'
    );

    /*
 * Customer Reviews
 */

    $router->get(
        '/products/{productId}/reviews',
        [ReviewController::class, 'index'],
        [],
        'products.reviews.index'
    );

    $router->get(
        '/reviews/mine',
        [ReviewController::class, 'mine'],
        [AuthMiddleware::class],
        'reviews.mine'
    );

    $router->post(
        '/reviews',
        [ReviewController::class, 'store'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'reviews.store'
    );


    /*
 * Customer Notifications
 */

    $router->get(
        '/notifications',
        [NotificationController::class, 'index'],
        [AuthMiddleware::class],
        'notifications.index'
    );

    $router->get(
        '/notifications/unread',
        [NotificationController::class, 'unread'],
        [AuthMiddleware::class],
        'notifications.unread'
    );

    $router->get(
        '/notifications/count',
        [NotificationController::class, 'count'],
        [AuthMiddleware::class],
        'notifications.count'
    );

    $router->get(
        '/notifications/{id}',
        [NotificationController::class, 'show'],
        [AuthMiddleware::class],
        'notifications.show'
    );

    $router->patch(
        '/notifications/{id}/read',
        [NotificationController::class, 'markAsRead'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'notifications.read'
    );

    $router->patch(
        '/notifications/read-all',
        [NotificationController::class, 'markAllAsRead'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'notifications.read-all'
    );

    /*
 * SEO
 */

    $router->get(
        '/seo/robots',
        [SeoController::class, 'robots'],
        [],
        'seo.robots'
    );

    $router->get(
        '/seo/sitemap',
        [SeoController::class, 'sitemap'],
        [],
        'seo.sitemap'
    );

    $router->get(
        '/payments/attempts/{id}',
        [PaymentController::class, 'show'],
        [
            AuthMiddleware::class,
        ],
        'payments.attempts.show'
    );

    $router->get(
        '/payments/attempts/by-order/{orderId}',
        [PaymentController::class, 'byOrder'],
        [
            AuthMiddleware::class,
        ],
        'payments.attempts.by-order'
    );

    $router->post(
        '/coupons/validate',
        [CouponController::class, 'validateCustomer'],
        [
            AuthMiddleware::class,
            CsrfMiddleware::class,
        ],
        'coupons.validate'
    );

    $router->get(
        '/storefront/products/complete-setup',
        [StorefrontController::class, 'completeYourSetup'],
        [],
        'storefront.product.complete-setup'
    );

    /*
     * Smart Commerce — public product bundle suggestions.
     */
    $router->get(
        '/storefront/smart-bundles',
        [SmartBundleController::class, 'index'],
        [],
        'storefront.smart-bundles'
    );

    $router->get(
        '/storefront/products/comparison',
        [ComparisonController::class, 'index'],
        [],
        'storefront.products.comparison'
    );

    $router->get(
        '/storefront/products/alternative',
        [AlternativeController::class, 'index'],
        [],
        'storefront.products.alternative'
    );

    $router->get(
        '/storefront/budget-optimizer',
        [BudgetOptimizerController::class, 'optimize'],
        [],
        'storefront.budget-optimizer'
    );


    /*
     * Admin Master Capabilities — notifications, incidents, support,
     * analytics, preferences, saved views and content revisions.
     */
    $adminCapabilityRead = [
        AdminAuthMiddleware::class,
        AdminRoutePermissionMiddleware::class,
    ];
    $adminCapabilityWrite = [
        AdminAuthMiddleware::class,
        AdminRoutePermissionMiddleware::class,
        CsrfMiddleware::class,
    ];

    $router->get('/admin/access/sessions', [AdminAccessController::class, 'sessions'], $adminCapabilityRead, 'admin.access.sessions.index');
    $router->get('/admin/access/sessions/history', [AdminAccessController::class, 'sessionsHistory'], $adminCapabilityRead, 'admin.access.sessions.history');
    $router->delete('/admin/access/sessions/{id}', [AdminAccessController::class, 'revokeSession'], $adminCapabilityWrite, 'admin.access.sessions.destroy');
    $router->get('/admin/access/admins', [AdminAccessController::class, 'admins'], $adminCapabilityRead, 'admin.access.admins.index');
    $router->get('/admin/access/admins/{id}/effective-permissions', [AdminAccessController::class, 'effectivePermissions'], $adminCapabilityRead, 'admin.access.admins.effective-permissions');
    $router->post('/admin/access/admins', [AdminAccessController::class, 'createAdmin'], $adminCapabilityWrite, 'admin.access.admins.store');
    $router->patch('/admin/access/admins/{id}', [AdminAccessController::class, 'updateAdmin'], $adminCapabilityWrite, 'admin.access.admins.update');
    $router->delete('/admin/access/admins/{id}', [AdminAccessController::class, 'deleteAdmin'], $adminCapabilityWrite, 'admin.access.admins.destroy');
    $router->put('/admin/access/admins/{id}/roles', [AdminAccessController::class, 'assignAdminRoles'], $adminCapabilityWrite, 'admin.access.admins.roles.update');

    $router->get('/admin/access/roles', [AdminAccessController::class, 'roles'], $adminCapabilityRead, 'admin.access.roles.index');
    $router->post('/admin/access/roles', [AdminAccessController::class, 'createRole'], $adminCapabilityWrite, 'admin.access.roles.store');
    $router->patch('/admin/access/roles/{id}', [AdminAccessController::class, 'updateRole'], $adminCapabilityWrite, 'admin.access.roles.update');
    $router->get('/admin/access/permissions', [AdminAccessController::class, 'permissions'], $adminCapabilityRead, 'admin.access.permissions.index');
    $router->get('/admin/access/roles/{id}/permissions', [AdminAccessController::class, 'rolePermissions'], $adminCapabilityRead, 'admin.access.roles.permissions.show');
    $router->put('/admin/access/roles/{id}/permissions', [AdminAccessController::class, 'assignRolePermissions'], $adminCapabilityWrite, 'admin.access.roles.permissions.update');

    $router->get('/admin/capabilities/notifications', [AdminCapabilityController::class, 'notifications'], $adminCapabilityRead, 'admin.capabilities.notifications.index');
    $router->post('/admin/capabilities/notifications', [AdminCapabilityController::class, 'createNotification'], $adminCapabilityWrite, 'admin.capabilities.notifications.store');
    $router->patch('/admin/capabilities/notifications/{id}/read', [AdminCapabilityController::class, 'markNotificationRead'], $adminCapabilityWrite, 'admin.capabilities.notifications.read');
    $router->patch('/admin/capabilities/notifications/{id}/acknowledge', [AdminCapabilityController::class, 'acknowledgeNotification'], $adminCapabilityWrite, 'admin.capabilities.notifications.acknowledge');
    $router->post('/admin/capabilities/notifications/read-all', [AdminCapabilityController::class, 'markAllNotificationsRead'], $adminCapabilityWrite, 'admin.capabilities.notifications.read-all');
    $router->get('/admin/capabilities/notification-preferences', [AdminCapabilityController::class, 'notificationPreferences'], $adminCapabilityRead, 'admin.capabilities.notification-preferences.index');
    $router->put('/admin/capabilities/notification-preferences', [AdminCapabilityController::class, 'saveNotificationPreference'], $adminCapabilityWrite, 'admin.capabilities.notification-preferences.update');
    $router->get('/admin/capabilities/notification-policies', [AdminCapabilityController::class, 'notificationPolicies'], $adminCapabilityRead, 'admin.capabilities.notification-policies.index');
    $router->put('/admin/capabilities/notification-policies', [AdminCapabilityController::class, 'saveNotificationPolicy'], $adminCapabilityWrite, 'admin.capabilities.notification-policies.update');

    $router->get('/admin/capabilities/incidents/analysis', [AdminIncidentController::class, 'analysis'], $adminCapabilityRead, 'admin.capabilities.incidents.analysis');
    $router->get('/admin/capabilities/incidents/{id}/timeline', [AdminIncidentController::class, 'timeline'], $adminCapabilityRead, 'admin.capabilities.incidents.timeline');
    $router->get('/admin/capabilities/jobs/health', [AdminIncidentController::class, 'jobs'], $adminCapabilityRead, 'admin.capabilities.jobs.health');
    $router->get('/admin/capabilities/incidents', [AdminCapabilityController::class, 'incidents'], $adminCapabilityRead, 'admin.capabilities.incidents.index');
    $router->patch('/admin/capabilities/incidents/{id}', [AdminCapabilityController::class, 'updateIncident'], $adminCapabilityWrite, 'admin.capabilities.incidents.update');

    $router->get('/admin/capabilities/tickets', [AdminCapabilityController::class, 'tickets'], $adminCapabilityRead, 'admin.capabilities.tickets.index');
    $router->get('/admin/capabilities/tickets/{id}', [AdminCapabilityController::class, 'showTicket'], $adminCapabilityRead, 'admin.capabilities.tickets.show');
    $router->post('/admin/capabilities/tickets', [AdminCapabilityController::class, 'createTicket'], $adminCapabilityWrite, 'admin.capabilities.tickets.store');
    $router->post('/admin/capabilities/tickets/{id}/messages', [AdminCapabilityController::class, 'addTicketMessage'], $adminCapabilityWrite, 'admin.capabilities.tickets.messages.store');
    $router->patch('/admin/capabilities/tickets/{id}', [AdminCapabilityController::class, 'updateTicket'], $adminCapabilityWrite, 'admin.capabilities.tickets.update');
    $router->get('/admin/capabilities/support/metrics', [AdminCapabilityController::class, 'supportMetrics'], $adminCapabilityRead, 'admin.capabilities.support.metrics');
    $router->get('/admin/capabilities/support/reports', [AdminCapabilityController::class, 'supportReports'], $adminCapabilityRead, 'admin.capabilities.support.reports');
    $router->get('/admin/capabilities/support/sla', [AdminCapabilityController::class, 'supportSla'], $adminCapabilityRead, 'admin.capabilities.support.sla.index');
    $router->put('/admin/capabilities/support/sla/{priority}', [AdminCapabilityController::class, 'saveSupportSla'], $adminCapabilityWrite, 'admin.capabilities.support.sla.update');

    $router->get('/admin/capabilities/product-analytics', [AdminCapabilityController::class, 'analytics'], $adminCapabilityRead, 'admin.capabilities.analytics.index');
    $router->get('/admin/capabilities/health', [AdminCapabilityController::class, 'health'], $adminCapabilityRead, 'admin.capabilities.health.index');
    $router->get('/admin/capabilities/search', [AdminCapabilityController::class, 'globalSearch'], $adminCapabilityRead, 'admin.capabilities.search.index');
    $router->post('/incident/client', [IncidentCaptureController::class, 'client'], [CsrfMiddleware::class], 'incident.client.capture');
    $router->post('/analytics/product-event', [ProductAnalyticsController::class, 'store'], [CsrfMiddleware::class], 'analytics.product-event.store');
    $router->get('/support/tickets', [CustomerSupportController::class, 'index'], [AuthMiddleware::class], 'support.tickets.index');
    $router->get('/support/tickets/{id}', [CustomerSupportController::class, 'show'], [AuthMiddleware::class], 'support.tickets.show');
    $router->post('/support/tickets', [CustomerSupportController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class], 'support.tickets.store');
    $router->post('/support/tickets/{id}/messages', [CustomerSupportController::class, 'reply'], [AuthMiddleware::class, CsrfMiddleware::class], 'support.tickets.messages.store');


    $router->get('/admin/capabilities/preferences', [AdminCapabilityController::class, 'preferences'], $adminCapabilityRead, 'admin.capabilities.preferences.index');
    $router->put('/admin/capabilities/preferences', [AdminCapabilityController::class, 'savePreference'], $adminCapabilityWrite, 'admin.capabilities.preferences.update');

    $router->get('/admin/capabilities/saved-views', [AdminCapabilityController::class, 'savedViews'], $adminCapabilityRead, 'admin.capabilities.saved-views.index');
    $router->post('/admin/capabilities/saved-views', [AdminCapabilityController::class, 'saveView'], $adminCapabilityWrite, 'admin.capabilities.saved-views.store');
    $router->delete('/admin/capabilities/saved-views/{id}', [AdminCapabilityController::class, 'deleteView'], $adminCapabilityWrite, 'admin.capabilities.saved-views.destroy');

    $router->get('/admin/capabilities/revisions', [AdminCapabilityController::class, 'revisions'], $adminCapabilityRead, 'admin.capabilities.revisions.index');
    $router->post('/admin/capabilities/revisions', [AdminCapabilityController::class, 'createRevision'], $adminCapabilityWrite, 'admin.capabilities.revisions.store');


    /* Phase 5 — structured page and section CMS. */
    $router->get('/admin/content/pages', [AdminContentController::class, 'pages'], $adminCapabilityRead, 'admin.content.pages.index');
    $router->get('/admin/content/sections', [AdminContentController::class, 'sections'], $adminCapabilityRead, 'admin.content.sections.index');
    $router->post('/admin/content/sections', [AdminContentController::class, 'createSection'], $adminCapabilityWrite, 'admin.content.sections.store');
    $router->patch('/admin/content/sections/{id}', [AdminContentController::class, 'updateSection'], $adminCapabilityWrite, 'admin.content.sections.update');
    $router->post('/admin/content/sections/{id}/publish', [AdminContentController::class, 'publish'], $adminCapabilityWrite, 'admin.content.sections.publish');
    $router->post('/admin/content/sections/{id}/schedule', [AdminContentController::class, 'schedule'], $adminCapabilityWrite, 'admin.content.sections.schedule');
    $router->delete('/admin/content/sections/{id}', [AdminContentController::class, 'deleteSection'], $adminCapabilityWrite, 'admin.content.sections.destroy');
    $router->post('/admin/content/sections/{id}/restore', [AdminContentController::class, 'restoreDeleted'], $adminCapabilityWrite, 'admin.content.sections.restore');
    $router->post('/admin/content/sections/reorder', [AdminContentController::class, 'reorder'], $adminCapabilityWrite, 'admin.content.sections.reorder');
    $router->get('/admin/content/sections/{id}/revisions', [AdminContentController::class, 'revisions'], $adminCapabilityRead, 'admin.content.sections.revisions.index');
    $router->post('/admin/content/sections/{id}/revisions/{revisionId}/restore', [AdminContentController::class, 'restoreRevision'], $adminCapabilityWrite, 'admin.content.sections.revisions.restore');

    $router->get('/admin/media', [AdminContentController::class, 'media'], $adminCapabilityRead, 'admin.media.index');
    $router->get('/admin/media/{id}/file', [CmsMediaController::class, 'adminShow'], $adminCapabilityRead, 'admin.media.file');
    $router->post('/admin/media', [AdminContentController::class, 'uploadMedia'], $adminCapabilityWrite, 'admin.media.store');
    $router->patch('/admin/media/{id}', [AdminContentController::class, 'updateMedia'], $adminCapabilityWrite, 'admin.media.update');
    $router->delete('/admin/media/{id}', [AdminContentController::class, 'deleteMedia'], $adminCapabilityWrite, 'admin.media.destroy');
    $router->get('/content-media/{id}', [CmsMediaController::class, 'show'], [], 'content-media.show');

};
