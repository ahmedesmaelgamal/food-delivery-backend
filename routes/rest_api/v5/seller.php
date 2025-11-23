    <?php

    use App\Enums\ViewPaths\Admin\Product;
    use App\Enums\ViewPaths\Vendor\Auth;
    use App\Http\Controllers\RestAPI\v5\orders\checkoutOrderController;
    use App\Http\Controllers\RestAPI\v5\user\auth\AuthController;
    use App\Http\Controllers\RestAPI\v5\user\auth\ForgotPasswordController;
    use App\Http\Controllers\RestAPI\v5\user\auth\LoginController as UserLoginController;
    use App\Http\Controllers\RestAPI\v5\user\profile\UserController;
    use App\Http\Controllers\RestAPI\v5\product\ProductController;
    use App\Http\Controllers\RestAPI\v5\user\general\GeneralController;
    use App\Http\Controllers\RestAPI\v5\user\profile\UserAddressController;
    use App\Http\Controllers\Wordpress\WordpressController;
    use App\Http\Controllers\Wordpress\WordpressCategoryController;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\RestAPI\v5\Payment\PaymentMethodsController;
  use App\Http\Controllers\RestAPI\v5\user\contactUs\ContactUsController;
    use App\Http\Controllers\RestAPI\v5\orders\MaximPaymentController;
    use App\Http\Middleware\VerifyWordpressWebhook;
    use App\Http\Controllers\Wordpress\WordPressWebhookController;

    /*
    |--------------------------------------------------------------------------
    | Seller Mobile APP API Routes
    |--------------------------------------------------------------------------
    |*/

    Route::get('/v5/seller/auth/test', function () {
        return response()->json(['message' => 'Seller API is working'], 200);
    });

    // Route::get('/v5/test',[WordpressController::class,'index']);
    Route::get('/v5/syncAllProductsInBatches', [WordpressController::class, 'syncAllProductsInBatches']);
    Route::get('/v5/syncAllOrdersInBatches', [WordpressController::class, 'syncAllOrdersInBatches']);
    Route::get('/v5/syncAllCouponsInBatches', [WordpressController::class, 'syncAllCouponsInBatches']);
    Route::get('/v5/syncAllProductVariantsInBatches', [WordpressController::class, 'syncAllProductVariantsInBatches']);
    Route::get('/v5/syncAllCategoriesInBatches', [WordpressCategoryController::class, 'syncAllCategoriesInBatches']);
    Route::get('/v5/syncAllCustomersInBatches', [WordpressController::class, 'syncAllCustomersInBatches']);
    Route::get('/v5/syncAllShippingZonesInBatches', [WordpressController::class, 'syncAllShippingZonesInBatches']);
    Route::get('/v5/syncAllShippingZoneLocationsInBatches', [WordpressController::class, 'syncAllShippingZoneLocationsInBatches']);
    Route::get('/v5/syncAllShippingZoneMethodsInBatches', [WordpressController::class, 'syncAllShippingZoneMethodsInBatches']);
    Route::get('/v5/syncAllBuyItTogetherInBatches', [WordpressController::class, 'syncAllBuyItTogetherInBatches']);
    Route::get('/v5/syncAllCitiesInBatches', [WordpressController::class, 'syncAllCitiesInBatches']);
    Route::get('/v5/syncAllPartnersInBatches', [WordpressController::class, 'syncAllPartnersInBatches']);
    Route::get('/v5/syncAllBannerInBatches', [WordpressController::class, 'syncAllBannerInBatches']);
    Route::get('/v5/syncAllProductsInArabicInBatches', [WordpressController::class, 'syncAllProductsInArabicInBatches']);
    Route::get('/v5/syncAllCategoriesInArabicInBatches', [WordpressCategoryController::class, 'syncAllCategoriesInArabicInBatches']);

    // routes/api.php
    Route::prefix('/v5/wordpress/webhooks')->name('webhooks.wordpress.')->group(function () {
        // WooCommerce Order webhooks
        Route::post('order/create', [WordPressWebhookController::class, 'orderCreate'])
            ->name('order.create')
            ->middleware([VerifyWordPressWebhook::class]);

        Route::post('order/update', [WordPressWebhookController::class, 'orderUpdate'])
            ->name('order.update')
            ->middleware([VerifyWordPressWebhook::class]);
        Route::post('order/delete', [WordPressWebhookController::class, 'orderDelete'])
            ->name('order.delete')
            ->middleware([VerifyWordPressWebhook::class]);

        // Product webhooks
        Route::post('/product/create', [WordPressWebhookController::class, 'productCreate'])
            ->name('product.create')
            ->middleware([VerifyWordPressWebhook::class]);
        Route::post('product/update', [WordPressWebhookController::class, 'productUpdate'])
            ->name('product.update')
            ->middleware([VerifyWordPressWebhook::class]);
        Route::post('product/delete', [WordPressWebhookController::class, 'productDelete'])
            ->name('product.delete')
            ->middleware([VerifyWordPressWebhook::class]);

        // Customer webhooks
        Route::post('customer/create', [WordPressWebhookController::class, 'customerCreate'])
            ->name('customer.create')
            ->middleware([VerifyWordPressWebhook::class]);
        Route::post('customer/update', [WordPressWebhookController::class, 'customerUpdate'])
            ->name('customer.update')
            ->middleware([VerifyWordPressWebhook::class]);
        Route::post('customer/delete', [WordPressWebhookController::class, 'customerDelete'])
            ->name('customer.delete')
            ->middleware([VerifyWordPressWebhook::class]);

        // Coupon webhooks
        Route::post('coupon/create', [WordPressWebhookController::class, 'couponCreate'])
            ->name('coupon.create')
            ->middleware([VerifyWordPressWebhook::class]);
        Route::post('coupon/update', [WordPressWebhookController::class, 'couponUpdate'])
            ->name('coupon.update')
            ->middleware([VerifyWordPressWebhook::class]);
        Route::post('coupon/delete', [WordPressWebhookController::class, 'couponDelete'])
            ->name('coupon.delete')
            ->middleware([VerifyWordPressWebhook::class]);
    });




    Route::post('/payment/session', [MaximPaymentController::class, 'createSession']);
    Route::get('/payment/status/{orderId}', [MaximPaymentController::class, 'checkStatus']);




    Route::group(['namespace' => 'RestAPI\v5\user', 'prefix' => 'v5/user', 'middleware' => ['api_lang']], function () {
        Route::group(['prefix' => 'auth', 'namespace' => 'auth'], function () {
            Route::controller(AuthController::class)->group(function () {
                Route::post('login', 'login');
                Route::post('login-with-social', 'loginWithSocial');
                Route::post('reset-password-request', 'resetPasswordRequest');
                Route::post('reset-password-submit',  'resetPasswordSubmit');
            });
        });

        Route::group(['prefix' => 'registration', 'namespace' => 'auth'], function () {
            Route::post('/validate-data',  'AuthController@validateData');
            Route::post('/register', 'AuthController@register');
        });

        Route::middleware(['jwt.auth'])->group(function () {
            Route::post('/store-fcm-token', [\App\Http\Controllers\RestAPI\v5\general\GeneralController::class, 'storeFcmToken']);
            Route::post('/logout', [\App\Http\Controllers\RestAPI\v5\user\auth\AuthController::class, 'logout']);
        });
        Route::post('/store-message', [ContactUsController::class, 'send']);

    });
    Route::group(['prefix' => 'v5/home', 'middleware' => ['api_lang', 'optional.auth']], function () {
        Route::get('/get-home',  [\App\Http\Controllers\RestAPI\v5\home\HomeController::class, 'getHome']);
        Route::get('/get-settings',  [\App\Http\Controllers\RestAPI\v5\home\HomeController::class, 'getSettings']);
        Route::get('/get-offers',  [\App\Http\Controllers\RestAPI\v5\home\HomeController::class, 'getOffers']);
        Route::get('/get-partners',  [\App\Http\Controllers\RestAPI\v5\home\HomeController::class, 'getPartners']);
        Route::get('/get-offers',  [\App\Http\Controllers\RestAPI\v5\home\HomeController::class, 'getOffers']);
        Route::get('/get-product-details', [\App\Http\Controllers\RestAPI\v5\home\HomeController::class, 'getProductDetails']);
//        Route::get('/get-product-details', [\App\Http\Controllers\RestAPI\v5\home\HomeController::class, 'getProductDetails']);
    });

    Route::group(['prefix' => 'v5/notification', 'middleware' => ['api_lang', "jwt.auth"]], function () {
        Route::post('/sendFcm',  [\App\Http\Controllers\RestAPI\v5\notification\NotificationController::class, 'sendFcmNotification']);
    });


    Route::group(['namespace' => 'RestAPI\v5\orders', 'prefix' => 'v5/orders', 'middleware' => ['api_lang', "jwt.auth"]], function () {
        Route::post('/checkout', [checkoutOrderController::class, 'checkout']);
        Route::post('/update-order-status',[checkoutOrderController::class,'updateOrderStatus']);
        Route::post('/cancel-order', [checkoutOrderController::class, 'cancelOrder']);
        Route::get('/re-order/{id}', [checkoutOrderController::class, 'reOrder']);
        Route::post('/call-back', [checkoutOrderController::class, 'call_back']);
        Route::get('/get-orders', [checkoutOrderController::class, 'getOrders']);
        Route::get('/get-order-details', [checkoutOrderController::class, 'getOrderDetails']);
        Route::get('/get-payment-methods', [checkoutOrderController::class, 'getPaymentMethods']);
        Route::get('/get-order-products', [checkoutOrderController::class, 'getOrderProducts']);

    });
    Route::group([
        'namespace' => 'RestAPI\v5\user\profile',
        'prefix' => 'v5/user',
        'middleware' => ['api_lang', 'jwt.auth']
    ], function () {
        Route::get('/get-data', [UserController::class, 'getData']);
        Route::post('/add-to-cart', [UserController::class, 'addToCart']);
        Route::post('/clear-cart', [UserController::class, 'clearCart']);
        Route::get('/get-cart', [UserController::class, 'getCart']);
        Route::get('/get-my-notifications',  [UserController::class, 'getMyNotifications']);
        Route::post('/update-profile', [UserController::class, 'updateProfile']);
        Route::post('/update-password', [UserController::class, 'updatePassword']);
        Route::delete('/delete-account', [UserController::class, 'deleteAccount']);
        // user address routes
        Route::get('/user-addresses', [UserAddressController::class, 'getData']);
        Route::post('/store-user-address', [UserAddressController::class, 'store']);
        Route::post('/update-user-address/{id}', [UserAddressController::class, 'update']);
        Route::post('/delete-user-address/{id}', [UserAddressController::class, 'destroy']);
        // contact us route
//         Route::post('/store-message',  [UserController::class, 'storeMessage']);
        Route::post('/add-to-wishlist', [UserController::class, 'add_to_wishlist']);
        Route::delete('/remove-from-wishlist', [UserController::class, 'remove_from_wishlist']);
        Route::get('/get-wishlist', [UserController::class, 'get_wishlist']);
        Route::get('/get-wishlist', [UserController::class, 'get_wishlist']);
        Route::post('/toggle-wishlist', [UserController::class, 'toggle_wishlist']);
    });

    Route::group([
        'namespace' => 'RestAPI\v5\product',
        'prefix' => 'v5/product',
        'middleware' => ['api_lang', 'optional.auth']
    ], function () {
        Route::post('/rate-product', [ProductController::class, 'submitProductReview']);
        Route::get('/get-shipping-zones', [ProductController::class, 'getShippingZones']);
        Route::get('/get-shipping-zone-methods', [ProductController::class, 'getShippingZoneMethods']);
        Route::get('/get-shipping-zone-methods-enabled', [ProductController::class, 'getShippingZoneMethodsEnabled']);
        Route::get('/get-shipping-zones-locations', [ProductController::class, 'getShippingZoneLocations']);
        Route::get('/get-cities', [ProductController::class, 'getCities']);
        Route::get('/get-products', [ProductController::class, 'get_products']);
        Route::get('/get_buy_it_together_products/{id}', [ProductController::class, 'get_buy_it_together_products']);
        Route::get('/get-category', [ProductController::class, 'getCategory']);
        Route::get('/get-latest-products', [ProductController::class, 'get_latest_products']);
        Route::get('/get-new-arrival-products', [ProductController::class, 'getNewArrivalProducts']);
        Route::get('/get-featured-products', [ProductController::class, 'getFeaturedProductsList']);
        Route::get('/get-top-rated-products', [ProductController::class, 'getTopRatedProducts']);
        Route::get('/get-searched-products', [ProductController::class, 'get_searched_products']);
        Route::get('/get-products-filter', [ProductController::class, 'getProductsFilter']);
        Route::get('/get-suggestion-products', [ProductController::class, 'get_suggestion_product']);
//      Route::get( '/get-product-details/{id}', [ProductController::class, 'getProductDetails']);
        Route::get('/get-best-selling-products', [ProductController::class, 'getBestSellingProducts']);
        Route::get('/get-home-categories', [ProductController::class, 'get_home_categories']);
        Route::get('/get-related-products/{id}', [ProductController::class, 'get_related_products']);
        Route::get('/get-product-reviews', [ProductController::class, 'get_product_reviews']);
        Route::get('/get-product-reviews-by-order', [ProductController::class, 'getProductReviewByOrder']);
        Route::get('/delete-review-image', [ProductController::class, 'deleteReviewImage']);
        Route::get('/get-product-rating', [ProductController::class, 'get_product_rating']);
        Route::get('/products-variety', [ProductController::class, 'products_variety']);
        Route::get('/whole-sale-products', [ProductController::class, 'whole_sale_products']);
        Route::get('/whole-sale-categories', [ProductController::class, 'whole_sale_categories']);
        Route::get('/coupon-check', [ProductController::class, 'coupon_check']);

        Route::get('/link-storage', function () {
            \Illuminate\Support\Facades\Artisan::call('storage:link');
            return response()->json(['message' => 'Storage linked successfully'], 200);
        });
    });



//    Route::group([
//        'namespace' => 'RestAPI\v5\product',
//        'prefix' => 'v5/product',
//        'middleware' => ['api_lang', 'jwt.auth']
//    ], function () {
//    });
