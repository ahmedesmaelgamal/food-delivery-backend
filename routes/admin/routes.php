<?php

use App\Enums\ViewPaths\Admin\ClearanceSale;
use App\Enums\ViewPaths\Admin\POS;
use App\Enums\ViewPaths\Admin\Cart;
use App\Enums\ViewPaths\Admin\Mail;
use App\Enums\ViewPaths\Admin\Coupon;
use App\Enums\ViewPaths\Admin\Review;
use App\Enums\ViewPaths\Admin\Vendor;
use App\Http\Controllers\Admin\BusinessSettings\WebsiteSetupController;
use App\Http\Controllers\Admin\Promotion\ClearanceSaleController;
use App\Http\Controllers\Admin\Promotion\ClearanceSalePrioritySetupController;
use App\Http\Controllers\Admin\Promotion\ClearanceSaleVendorOfferController;
use App\Http\Controllers\Admin\Settings\FirebaseOTPVerificationController;
use App\Http\Controllers\FirebaseController;
use Illuminate\Support\Facades\Route;
use App\Enums\ViewPaths\Admin\Contact;
use App\Enums\ViewPaths\Admin\SiteMap;
use App\Enums\ViewPaths\Admin\Category;
use App\Enums\ViewPaths\Admin\Chatting;
use App\Enums\ViewPaths\Admin\Customer;
use App\Enums\ViewPaths\Admin\Employee;
use App\Enums\ViewPaths\Admin\FlashDeal;
use App\Enums\ViewPaths\Admin\HelpTopic;
use App\Enums\ViewPaths\Admin\DeliveryMan;
use App\Enums\ViewPaths\Admin\FeatureDeal;
use App\Enums\ViewPaths\Admin\SubCategory;
use App\Http\Controllers\SharedController;
use App\Enums\ViewPaths\Admin\DealOfTheDay;
use App\Enums\ViewPaths\Admin\Notification;
use App\Enums\ViewPaths\Admin\ShippingType;
use App\Enums\ViewPaths\Admin\EmailTemplate;
use App\Enums\ViewPaths\Admin\CustomerWallet;
use App\Enums\ViewPaths\Admin\SubSubCategory;
use App\Enums\ViewPaths\Admin\DeliveryManCash;
use App\Enums\ViewPaths\Admin\EmergencyContact;
use App\Enums\ViewPaths\Admin\WithdrawalMethod;
use App\Enums\ViewPaths\Admin\RefundTransaction;
use App\Enums\ViewPaths\Admin\RobotsMetaContent;
use App\Http\Controllers\Admin\ReportController;
use App\Enums\ViewPaths\Admin\InhouseProductSale;
use App\Http\Controllers\Admin\POS\POSController;
use App\Http\Controllers\Admin\ProfileController;
use App\Enums\ViewPaths\Admin\DeliverymanWithdraw;
use App\Http\Controllers\Admin\ChattingController;
use App\Http\Controllers\Admin\POS\CartController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Order\OrderController;
use App\Http\Controllers\Admin\OrderReportController;
use App\Http\Controllers\Admin\Order\RefundController;
use App\Http\Controllers\Admin\POS\POSOrderController;
use App\Enums\ViewPaths\Admin\VendorRegistrationReason;
use App\Http\Controllers\Admin\Product\BrandController;
use App\Http\Controllers\Admin\ProductReportController;
use App\Http\Controllers\Admin\Vendor\VendorController;
use App\Enums\ViewPaths\Admin\VendorRegistrationSetting;
use App\Http\Controllers\Admin\EmailTemplatesController;
use App\Http\Controllers\Admin\Product\ReviewController;
use App\Http\Controllers\Admin\Settings\AddonController;
use App\Http\Controllers\Admin\Settings\PagesController;
use App\Http\Controllers\Admin\Settings\ThemeController;
use App\Http\Controllers\Admin\Product\ProductController;
use App\Http\Controllers\Admin\ThirdParty\MailController;
use App\Http\Controllers\Admin\Product\CategoryController;
use App\Http\Controllers\Admin\Promotion\BannerController;
use App\Http\Controllers\Admin\Promotion\CouponController;
use App\Http\Controllers\Admin\Settings\SiteMapController;
use App\Http\Controllers\Admin\Customer\CustomerController;
use App\Http\Controllers\Admin\Employee\EmployeeController;
use App\Http\Controllers\Admin\Product\AttributeController;
use App\Http\Controllers\Admin\Settings\CurrencyController;
use App\Http\Controllers\Admin\Settings\LanguageController;
use App\Http\Controllers\Admin\TransactionReportController;
use App\Http\Controllers\Admin\InhouseProductSaleController;
use App\Http\Controllers\Admin\ProductStockReportController;
use App\Http\Controllers\Admin\Settings\ErrorLogsController;
use App\Http\Controllers\Admin\Employee\CustomRoleController;
use App\Http\Controllers\Admin\Product\SubCategoryController;
use App\Http\Controllers\Admin\Promotion\FlashDealController;
use App\Http\Controllers\Admin\CategoryShippingCostController;
use App\Http\Controllers\Admin\Settings\FileManagerController;
use App\Http\Controllers\Admin\Settings\InhouseShopController;
use App\Http\Controllers\Admin\Settings\SEOSettingsController;
use App\Http\Controllers\Admin\ThirdParty\RecaptchaController;
use App\Http\Controllers\Admin\ThirdParty\SMSModuleController;
use App\Http\Controllers\Admin\ProductWishlistReportController;
use App\Http\Controllers\Admin\Shipping\ShippingTypeController;
use App\Http\Controllers\Admin\HelpAndSupport\ContactController;
use App\Http\Controllers\Admin\Product\SubSubCategoryController;
use App\Http\Controllers\Admin\Promotion\DealOfTheDayController;
use App\Http\Controllers\Admin\Promotion\FeaturedDealController;
use App\Http\Controllers\Admin\Promotion\MostDemandedController;
use App\Http\Controllers\Admin\Settings\OrderSettingsController;
use App\Http\Controllers\Admin\Settings\PrioritySetupController;
use App\Http\Controllers\Admin\Customer\CustomerWalletController;
use App\Http\Controllers\Admin\Deliveryman\DeliveryManController;
use App\Http\Controllers\Admin\Settings\SoftwareUpdateController;
use App\Http\Controllers\Admin\Settings\VendorSettingsController;
use App\Http\Controllers\Admin\Shipping\ShippingMethodController;
use App\Http\Controllers\Admin\ThirdParty\GoogleMapAPIController;
use App\Http\Controllers\Admin\Vendor\WithdrawalMethodController;
use App\Http\Controllers\Admin\VendorProductSaleReportController;
use App\Http\Controllers\Admin\Customer\CustomerLoyaltyController;
use App\Http\Controllers\Admin\HelpAndSupport\HelpTopicController;
use App\Http\Controllers\Admin\Report\RefundTransactionController;
use App\Http\Controllers\Admin\Settings\DatabaseSettingController;
use App\Http\Controllers\Admin\Settings\FeaturesSectionController;
use App\Http\Controllers\Admin\Settings\InvoiceSettingsController;
use App\Http\Controllers\Admin\ThirdParty\PaymentMethodController;
use App\Http\Controllers\Admin\Notification\NotificationController;
use App\Http\Controllers\Admin\Settings\BusinessSettingsController;
use App\Http\Controllers\Admin\Settings\RobotsMetaContentController;
use App\Http\Controllers\Admin\ThirdParty\SocialMediaChatController;
use App\Http\Controllers\Admin\Deliveryman\EmergencyContactController;
use App\Http\Controllers\Admin\HelpAndSupport\SupportTicketController;
use App\Http\Controllers\Admin\Payment\OfflinePaymentMethodController;
use App\Http\Controllers\Admin\Settings\DeliverymanSettingsController;
use App\Http\Controllers\Admin\Settings\DeliveryRestrictionController;
use App\Http\Controllers\Admin\Settings\EnvironmentSettingsController;
use App\Http\Controllers\Admin\Settings\SocialMediaSettingsController;
use App\Http\Controllers\Admin\SystemSetup\SystemLoginSetupController;
use App\Http\Controllers\Admin\ThirdParty\SocialLoginSettingsController;
use App\Http\Controllers\Admin\Deliveryman\DeliverymanWithdrawController;
use App\Http\Controllers\Admin\Settings\VendorRegistrationReasonController;
use App\Http\Controllers\Admin\Deliveryman\DeliveryManCashCollectController;
use App\Http\Controllers\Admin\Settings\StorageConnectionSettingsController;
use App\Http\Controllers\Admin\Settings\VendorRegistrationSettingController;
use App\Http\Controllers\Admin\Notification\PushNotificationSettingsController;


Route::controller(SharedController::class)->group(function () {
    Route::post('change-language', 'changeLanguage')->name('change-language');
    Route::post('get-session-recaptcha-code', 'getSessionRecaptchaCode')->name('get-session-recaptcha-code');
    Route::post('g-recaptcha-response-store', 'storeRecaptchaResponse')->name('g-recaptcha-response-store');
    Route::get('g-recaptcha-session-store', 'storeRecaptchaSession')->name('g-recaptcha-session-store');
});

Route::controller(FirebaseController::class)->group(function () {
    Route::post('system/subscribe-to-topic', 'subscribeToTopic')->name('system.subscribeToTopic');
});


Route::group(['prefix' => 'login'], function () {
    Route::get('{loginUrl}', [LoginController::class, 'index']);
    Route::get('recaptcha/{tmp}', [LoginController::class, 'generateReCaptcha'])->name('recaptcha');
    Route::post('/', [LoginController::class, 'login'])->name('login');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['admin']], function () {
    Route::get('component', function () {
        return view('layouts.admin.component');
    });
    Route::get('component-snippets', function () {
        return view('layouts.admin.component-snippets');
    });
    Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
        Route::controller(DashboardController::class)->group(function () {

            Route::get('', 'index')->name('index');
            Route::post('order-status', 'getOrderStatus')->name('order-status');
            Route::get('earning-statistics', 'getEarningStatistics')->name('earning-statistics');
            Route::get('order-statistics', 'getOrderStatistics')->name('order-statistics');
            Route::get('real-time-activities', 'getRealTimeActivities')->name('real-time-activities');
        });
    });

    Route::get('logout', [LoginController::class, 'logout'])->name('logout');

    Route::group(['prefix' => 'pos', 'as' => 'pos.', 'middleware' => ['module:pos_management']], function () {
        Route::controller(POSController::class)->group(function () {
            Route::get(POS::INDEX[URI], 'index')->name('index');
            Route::any(POS::CHANGE_CUSTOMER[URI], 'changeCustomer')->name('change-customer');
            Route::post(POS::UPDATE_DISCOUNT[URI], 'updateDiscount')->name('update-discount');
            Route::post(POS::COUPON_DISCOUNT[URI], 'getCouponDiscount')->name('coupon-discount');
            Route::get(POS::QUICK_VIEW[URI], 'getQuickView')->name('quick-view');
            Route::get(POS::SEARCH[URI], 'getSearchedProductsView')->name('search-product');
        });

        Route::controller(CartController::class)->group(function () {
            Route::post(Cart::VARIANT[URI], 'getVariantPrice')->name('get-variant-price');
            Route::post(Cart::QUANTITY_UPDATE[URI], 'updateQuantity')->name('update-quantity');
            Route::get(Cart::GET_CART_IDS[URI], 'getCartIds')->name('get-cart-ids');
            Route::get(Cart::CLEAR_CART_IDS[URI], 'clearSessionCartIds')->name('clear-cart-ids');
            Route::post(Cart::ADD[URI], 'addToCart')->name('add-to-cart');
            Route::post(Cart::REMOVE[URI], 'removeCart')->name('remove-cart');
            Route::any(Cart::CART_EMPTY[URI], 'emptyCart')->name('empty-cart');
            Route::any(Cart::CHANGE_CART[URI], 'changeCart')->name('change-cart');
            Route::get(Cart::NEW_CART_ID[URI], 'addNewCartId')->name('new-cart-id');
        });

        Route::controller(POSOrderController::class)->group(function () {
            Route::post('order-details/{id}', 'index')->name('order-details');
            Route::post('order-place', 'placeOrder')->name('place-order');
            Route::any('cancel-order', 'cancelOrder')->name('cancel-order');
            Route::any('view-hold-orders', 'getAllHoldOrdersView')->name('view-hold-orders');
        });
    });

    Route::group(['prefix' => 'profile', 'as' => 'profile.'], function () {
        Route::controller(ProfileController::class)->group(function () {
            Route::get('update/{id}', 'getUpdateView')->name('update');
            Route::post('update/{id}', 'update');
            Route::patch('update/{id}', 'updatePassword');
        });
    });

    Route::group(['prefix' => 'products', 'as' => 'products.', 'middleware' => ['module:product_management']], function () {
        Route::controller(ProductController::class)->group(function () {
            Route::get('list/{type}', 'index')->name('list');
            Route::get('add', 'getAddView')->name('add');
            Route::post('add', 'add')->name('store');
            Route::get('view/{addedBy}/{id}', 'getView')->name('view');
            Route::post('sku-combination', 'getSkuCombinationView')->name('sku-combination');
            Route::post('digital-variation-combination', 'getDigitalVariationCombinationView')->name('digital-variation-combination');
            Route::post('digital-variation-file-delete', 'deleteDigitalVariationFile')->name('digital-variation-file-delete');
            Route::post('featured-status', 'updateFeaturedStatus')->name('featured-status');
            Route::get('get-categories', 'getCategories')->name('get-categories');
            Route::post('status-update', 'updateStatus')->name('status-update');
            Route::get('barcode/{id}', 'getBarcodeView')->name('barcode');
            Route::get('export-excel/{type}', 'exportList')->name('export-excel');
            Route::get('stock-limit-list/{type}', 'getStockLimitListView')->name('stock-limit-list');
            Route::delete('delete/{id}', 'delete')->name('delete');
            Route::get('update/{id}', 'getUpdateView')->name('update');
            Route::post('update/{id}', 'update');
            Route::get('delete-image', 'deleteImage')->name('delete-image');
            Route::get('get-variations', 'getVariations')->name('get-variations');
            Route::post('update-quantity', 'updateQuantity')->name('update-quantity');
            Route::get('bulk-import', 'getBulkImportView')->name('bulk-import');
            Route::post('bulk-import', 'importBulkProduct');
            Route::get('updated-product-list', 'updatedProductList')->name('updated-product-list');
            Route::post('updated-shipping', 'updatedShipping')->name('updated-shipping');
            Route::post('deny', 'deny')->name('deny');
            Route::post('approve-status', 'approveStatus')->name('approve-status');
            Route::get('search', 'getSearchedProductsView')->name('search-product');
            Route::get('multiple-product-details', 'getMultipleProductDetailsView')->name('multiple-product-details');
            Route::get('product-gallery', 'getProductGalleryView')->name('product-gallery');
            Route::get('stock-limit-status/{type}', 'getStockLimitStatus')->name('stock-limit-status');
            Route::post('delete-preview-file', 'deletePreviewFile')->name('delete-preview-file');
            Route::get('request-restock-list', 'getRequestRestockListView')->name('request-restock-list');
            Route::get('export-restock', 'exportRestockList')->name('restock-export');
            Route::delete('restock-delete/{id}', 'deleteRestock')->name('restock-delete');
        });
    });

    Route::group(['prefix' => 'orders', 'as' => 'orders.', 'middleware' => ['module:order_management']], function () {
        Route::controller(OrderController::class)->group(function () {
            Route::get('list/{status}', 'index')->name('list');
            Route::get('export-excel/{status}', 'exportList')->name('export-excel');
            Route::get('generate-invoice/{id}', 'generateInvoice')->name('generate-invoice')->withoutMiddleware(['module:order_management']);
            Route::get('details/{id}', 'getView')->name('details');
            Route::post('address-update', 'updateAddress')->name('address-update');// update address from order details
            Route::post('update-deliver-info', 'updateDeliverInfo')->name('update-deliver-info');
            Route::get('add-delivery-man/{order_id}/{d_man_id}', 'addDeliveryMan')->name('add-delivery-man');
            Route::post('amount-date-update', 'updateAmountDate')->name('amount-date-update');
            Route::get('customers', 'getCustomers')->name('customers');
            Route::post('payment-status', 'updatePaymentStatus')->name('payment-status');
            Route::get('inhouse-order-filter', 'filterInHouseOrder')->name('inhouse-order-filter');
            Route::post('digital-file-upload-after-sell', 'uploadDigitalFileAfterSell')->name('digital-file-upload-after-sell');
            Route::post('status', 'updateStatus')->name('status');
        });
    });

    // Attribute
    Route::group(['prefix' => 'attribute', 'as' => 'attribute.', 'middleware' => ['module:product_management']], function () {
        Route::controller(AttributeController::class)->group(function () {
            Route::get('view', 'index')->name('view');
            Route::post('store', 'add')->name('store');
            Route::get('update/{id}', 'getUpdateView')->name('update');
            Route::post('update/{id}', 'update');
            Route::post('delete', 'delete')->name('delete');
        });
    });

    // Brand
    Route::group(['prefix' => 'brand', 'as' => 'brand.', 'middleware' => ['module:product_management']], function () {
        Route::controller(BrandController::class)->group(function () {
            Route::get('list', 'index')->name('list');
            Route::get('add-new', 'getAddView')->name('add-new');
            Route::post('add-new', 'add');
            Route::get('update/{id}', 'getUpdateView')->name('update');
            Route::post('update/{id}', 'update');
            Route::post('delete', 'delete')->name('delete');
            Route::get('export', 'exportList')->name('export');
            Route::post('status-update', 'updateStatus')->name('status-update');
        });
    });

    // Category
    Route::group(['prefix' => 'category', 'as' => 'category.', 'middleware' => ['module:product_management']], function () {
        Route::controller(CategoryController::class)->group(function () {
            Route::get(Category::LIST[URI], 'index')->name('view');
            Route::post(Category::ADD[URI], 'add')->name('store');
            Route::get(Category::UPDATE[URI], 'getUpdateView')->name('update');
            Route::post(Category::UPDATE[URI], 'update');
            Route::post(Category::DELETE[URI], 'delete')->name('delete');
            Route::post(Category::STATUS[URI], 'updateStatus')->name('status');
            Route::get(Category::EXPORT[URI], 'getExportList')->name('export');

        });
    });

    // Sub Category
    Route::group(['prefix' => 'sub-category', 'as' => 'sub-category.', 'middleware' => ['module:product_management']], function () {
        Route::controller(SubCategoryController::class)->group(function () {
            Route::get(SubCategory::LIST[URI], 'index')->name('view');
            Route::post(SubCategory::ADD[URI], 'add')->name('store');
            Route::get(SubCategory::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
            Route::post(SubCategory::UPDATE[URI] . '/{id}', 'update');
            Route::post(SubCategory::DELETE[URI], 'delete')->name('delete');
            Route::get(SubCategory::EXPORT[URI], 'getExportList')->name('export');
        });
    });

    // Sub Sub Category
    Route::group(['prefix' => 'sub-sub-category', 'as' => 'sub-sub-category.', 'middleware' => ['module:product_management']], function () {
        Route::controller(SubSubCategoryController::class)->group(function () {
            Route::get(SubSubCategory::LIST[URI], 'index')->name('view');
            Route::post(SubSubCategory::ADD[URI], 'add')->name('store');
            Route::get(SubSubCategory::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
            Route::post(SubSubCategory::UPDATE[URI] . '/{id}', 'update');
            Route::post(SubSubCategory::DELETE[URI], 'delete')->name('delete');
            Route::post(SubSubCategory::GET_SUB_CATEGORY[URI], 'getSubCategory')->name('getSubCategory');
            Route::get(SubSubCategory::EXPORT[URI], 'getExportList')->name('export');
        });
    });

    // Banner
    Route::group(['prefix' => 'banner', 'as' => 'banner.', 'middleware' => ['module:promotion_management']], function () {
        Route::controller(BannerController::class)->group(function () {
            Route::get('list', 'index')->name('list');
            Route::post('add', 'add')->name('store');
            Route::post('delete', 'delete')->name('delete');
            Route::post('status', 'updateStatus')->name('status');
            Route::get('update/{id}', 'getUpdateView')->name('update');
            Route::post('update/{id}', 'update');
        });
    });
    // Customer Routes, Customer wallet Routes, Customer Loyalty Routes
    Route::group(['prefix' => 'customer', 'as' => 'customer.', 'middleware' => ['module:user_section']], function () {
        Route::controller(CustomerController::class)->group(function () {
            Route::get(Customer::LIST[URI], 'getListView')->name('list');
            Route::get('view/{user_id}', 'getView')->name('view');
            Route::get(Customer::ORDER_LIST_EXPORT[URI] . '/{user_id}', 'exportOrderList')->name('order-list-export');
            Route::post('status-update', 'updateStatus')->name('status-update');
            Route::delete(Customer::DELETE[URI], 'delete')->name('delete');
            Route::get(Customer::SUBSCRIBER_LIST[URI], 'getSubscriberListView')->name('subscriber-list');
            Route::get(Customer::SUBSCRIBER_EXPORT[URI], 'exportSubscribersList')->name('subscriber-list.export');
            Route::get('export', 'exportList')->name('export');
            Route::get(Customer::SEARCH[URI], 'getCustomerList')->name('customer-list-search');
            Route::get(Customer::SEARCH_WITHOUT_ALL_CUSTOMER[URI], 'getCustomerListWithoutAllCustomerName')->name('customer-list-without-all-customer');
            Route::post(Customer::ADD[URI], 'add')->name('add');
        });

        Route::group(['prefix' => 'wallet', 'as' => 'wallet.'], function () {
            Route::controller(CustomerWalletController::class)->group(function () {
                Route::get('report', 'index')->name('report');
                Route::post(CustomerWallet::ADD[URI], 'addFund')->name('add-fund');
                Route::get(CustomerWallet::EXPORT[URI], 'exportList')->name('export');
                Route::get(CustomerWallet::BONUS_SETUP[URI], 'getBonusSetupView')->name('bonus-setup');
                Route::post(CustomerWallet::BONUS_SETUP[URI], 'addBonusSetup');
                Route::post(CustomerWallet::BONUS_SETUP_UPDATE[URI], 'update')->name('bonus-setup-update');
                Route::post('bonus-setup-status', 'updateStatus')->name('bonus-setup-status');
                Route::get(CustomerWallet::BONUS_SETUP_EDIT[URI] . '/{id}', 'getUpdateView')->name('bonus-setup-edit');
                Route::delete(CustomerWallet::BONUS_SETUP_DELETE[URI], 'deleteBonus')->name('bonus-setup-delete');
            });
        });

        Route::group(['prefix' => 'loyalty', 'as' => 'loyalty.'], function () {
            Route::controller(CustomerLoyaltyController::class)->group(function () {
                Route::get(Customer::LOYALTY_REPORT[URI], 'index')->name('report');
                Route::get(Customer::LOYALTY_EXPORT[URI], 'exportList')->name('export');
            });
        });

    });

    Route::group(['prefix' => 'report', 'as' => 'report.', 'middleware' => ['module:report']], function () {
        Route::controller(InhouseProductSaleController::class)->group(function () {
            Route::get(InhouseProductSale::VIEW[URI], 'index')->name('inhouse-product-sale');
        });
    });

    Route::group(['prefix' => 'vendors', 'as' => 'vendors.', 'middleware' => ['module:user_section']], function () {
        Route::controller(VendorController::class)->group(function () {
            Route::get(Vendor::LIST[URI], 'index')->name('vendor-list');
            Route::get(Vendor::ADD[URI], 'getAddView')->name('add');
            Route::POST(Vendor::ADD[URI], 'add');
            Route::get(Vendor::ORDER_LIST_EXPORT[URI] . '/{vendor_id}', 'exportOrderList')->name('order-list-export');
            Route::post(Vendor::STATUS[URI], 'updateStatus')->name('updateStatus');
            Route::get(Vendor::EXPORT[URI], 'exportList')->name('export');

            Route::post(Vendor::SALES_COMMISSION_UPDATE[URI] . '/{id}', 'updateSalesCommission')->name('sales-commission-update');
            Route::get(Vendor::ORDER_DETAILS[URI] . '/{order_id}/{vendor_id}', 'getOrderDetailsView')->name('order-details');
            Route::get(Vendor::VIEW[URI] . '/{id}/{tab?}', 'getView')->name('view');
            Route::post(Vendor::UPDATE_SETTING[URI] . '/{id}', 'updateSetting')->name('update-setting');

            Route::get(Vendor::WITHDRAW_LIST[URI], 'getWithdrawListView')->name('withdraw_list');
            Route::get(Vendor::WITHDRAW_LIST_EXPORT[URI], 'exportWithdrawList')->name('withdraw-list-export-excel');
            Route::get(Vendor::WITHDRAW_VIEW[URI] . '/{withdrawId}/{vendorId}', 'getWithdrawView')->name('withdraw_view');
            Route::post(Vendor::WITHDRAW_STATUS[URI] . '/{id}', 'withdrawStatus')->name('withdraw_status');
        });

        Route::group(['prefix' => 'withdraw-method', 'as' => 'withdraw-method.'], function () {
            Route::controller(WithdrawalMethodController::class)->group(function () {
                Route::get(WithdrawalMethod::LIST[URI], 'index')->name('list');
                Route::get(WithdrawalMethod::ADD[URI], 'getAddView')->name('add');
                Route::post(WithdrawalMethod::ADD[URI], 'add');
                Route::delete(WithdrawalMethod::DELETE[URI] . '/{id}', 'delete')->name('delete');
                Route::post(WithdrawalMethod::DEFAULT_STATUS[URI], 'updateDefaultStatus')->name('default-status');
                Route::post(WithdrawalMethod::STATUS[URI], 'updateStatus')->name('status-update');
                Route::get(WithdrawalMethod::UPDATE[URI] . '/{id}', 'getUpdateView')->name('edit');
                Route::post(WithdrawalMethod::UPDATE[URI], 'update')->name('update');
            });
        });
    });

    Route::group(['prefix' => 'employee', 'as' => 'employee.'], function () {
        Route::controller(EmployeeController::class)->group(function () {
            Route::get(Employee::LIST[URI], 'index')->name('list');
            Route::get(Employee::ADD[URI], 'getAddView')->name('add-new');
            Route::post(Employee::ADD[URI], 'add')->name('add-new-post');
            Route::get(Employee::EXPORT[URI], 'exportList')->name('export');
            Route::get(Employee::VIEW[URI] . '/{id}', 'getView')->name('view');
            Route::get(Employee::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
            Route::post(Employee::UPDATE[URI] . '/{id}', 'update');
            Route::post(Employee::STATUS[URI], 'updateStatus')->name('status');
        });
    });

    Route::group(['prefix' => 'custom-role', 'as' => 'custom-role.', 'middleware' => ['module:user_section']], function () {
        Route::controller(CustomRoleController::class)->group(function () {
            Route::get('add', 'index')->name('create');
            Route::post('add', 'add')->name('store');
            Route::get('update/{id}', 'getUpdateView')->name('update');
            Route::post('update/{id}', 'update');
            Route::post('employee-role-status', 'updateStatus')->name('employee-role-status');
            Route::post('delete', 'delete')->name('delete');
            Route::get('export', 'exportList')->name('export');
        });
    });

    /*  report */
    Route::group(['prefix' => 'report', 'as' => 'report.', 'middleware' => ['module:report']], function () {
        Route::group(['prefix' => 'transaction', 'as' => 'transaction.'], function () {
            Route::controller(RefundTransactionController::class)->group(function () {
                Route::get(RefundTransaction::INDEX[URI], 'index')->name('refund-transaction-list');
                Route::get(RefundTransaction::EXPORT[URI], 'exportRefundTransaction')->name('refund-transaction-export');
                Route::get(RefundTransaction::GENERATE_PDF[URI], 'getRefundTransactionPDF')->name('refund-transaction-summary-pdf');
            });
        });
    });

    Route::group(['prefix' => 'report', 'as' => 'report.', 'middleware' => ['module:report']], function () {
        Route::controller(ReportController::class)->group(function () {
            Route::get('earning', 'earning_index')->name('earning');
            Route::get('admin-earning', 'admin_earning')->name('admin-earning');
            Route::get('admin-earning-excel-export', 'exportAdminEarning')->name('admin-earning-excel-export');
            Route::post('admin-earning-duration-download-pdf', 'admin_earning_duration_download_pdf')->name('admin-earning-duration-download-pdf');
            Route::get('vendor-earning', 'vendorEarning')->name('vendor-earning');
            Route::get('vendor-earning-excel-export', 'exportVendorEarning')->name('vendor-earning-excel-export');
            Route::any('set-date', 'set_date')->name('set-date');
        });

        Route::controller(OrderReportController::class)->group(function () {
            Route::get('order', 'order_list')->name('order');
            Route::get('order-report-excel', 'orderReportExportExcel')->name('order-report-excel');
            Route::get('order-report-pdf', 'exportOrderReportInPDF')->name('order-report-pdf');
        });

        Route::controller(ProductReportController::class)->group(function () {
            Route::get('all-product', 'all_product')->name('all-product');
            Route::get('all-product-excel', 'allProductExportExcel')->name('all-product-excel');
        });

        Route::controller(VendorProductSaleReportController::class)->group(function () {
            Route::get('vendor-report', 'vendorReport')->name('vendor-report');
            Route::get('vendor-report-export', 'exportVendorReport')->name('vendor-report-export');
        });
    });

    Route::group(['prefix' => 'transaction', 'as' => 'transaction.', 'middleware' => ['module:report']], function () {
        Route::controller(TransactionReportController::class)->group(function () {
            Route::get('order-transaction-list', 'order_transaction_list')->name('order-transaction-list');
            Route::get('pdf-order-wise-transaction', 'pdf_order_wise_transaction')->name('pdf-order-wise-transaction');
            Route::get('order-transaction-export-excel', 'orderTransactionExportExcel')->name('order-transaction-export-excel');
            Route::get('order-transaction-summary-pdf', 'order_transaction_summary_pdf')->name('order-transaction-summary-pdf');
            Route::get('expense-transaction-list', 'getExpenseTransactionList')->name('expense-transaction-list');
            Route::get('pdf-order-wise-expense-transaction', 'pdf_order_wise_expense_transaction')->name('pdf-order-wise-expense-transaction');
            Route::get('expense-transaction-export-excel', 'expenseTransactionExportExcel')->name('expense-transaction-export-excel');
            Route::get('expense-transaction-summary-pdf', 'expense_transaction_summary_pdf')->name('expense-transaction-summary-pdf');

            Route::get('wallet-bonus', 'wallet_bonus')->name('wallet-bonus');
        });
    });

    Route::group(['prefix' => 'stock', 'as' => 'stock.', 'middleware' => ['module:report']], function () {
        Route::controller(ProductStockReportController::class)->group(function () {
            //product stock report
            Route::get('product-stock', 'index')->name('product-stock');
            Route::get('product-stock-export', 'export')->name('product-stock-export');
            Route::post('ps-filter', 'filter')->name('ps-filter');
        });

        //product in wishlist report
        Route::controller(ProductWishlistReportController::class)->group(function () {
            Route::get('product-in-wishlist', 'index')->name('product-in-wishlist');
            Route::get('wishlist-product-export', 'export')->name('wishlist-product-export');
        });
    });

    // Reviews
    Route::group(['prefix' => 'reviews', 'as' => 'reviews.', 'middleware' => ['module:user_section']], function () {
        Route::controller(ReviewController::class)->group(function () {
            Route::get('list', 'index')->name('list')->middleware('actch');
            Route::post('status', 'updateStatus')->name('status');
            Route::get(Review::EXPORT[URI], 'exportList')->name('export')->middleware('actch');
            Route::get(Review::SEARCH[URI], 'getCustomerList')->name('customer-list-search');
            Route::any(Review::SEARCH_PRODUCT[URI], 'search')->name('search-product');
            Route::post(Review::REVIEW_REPLY[URI], 'addReviewReply')->name('add-review-reply');
        });
    });

    // Coupon
    Route::group(['prefix' => 'coupon', 'as' => 'coupon.', 'middleware' => ['module:promotion_management']], function () {
        Route::controller(CouponController::class)->group(function () {
            Route::get(Coupon::ADD[URI], 'getAddListView')->name('add')->middleware('actch');
            Route::post(Coupon::ADD[URI], 'add');
            Route::get(Coupon::EXPORT[URI], 'exportList')->name('export')->middleware('actch');
            Route::get(Coupon::QUICK_VIEW[URI], 'quickView')->name('quick-view-details');
            Route::get(Coupon::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update')->middleware('actch');
            Route::post(Coupon::UPDATE[URI] . '/{id}', 'update');
            Route::get(Coupon::STATUS[URI] . '/{id}/{status}', 'updateStatus')->name('status');
            Route::post(Coupon::VENDOR_LIST[URI], 'getVendorList')->name('ajax-get-vendor');
            Route::delete(Coupon::DELETE[URI] . '/{id}', 'delete')->name('delete');
        });
    });

    Route::group(['prefix' => 'deal', 'as' => 'deal.', 'middleware' => ['module:promotion_management']], function () {
        Route::controller(FlashDealController::class)->group(function () {
            Route::get(FlashDeal::LIST[URI], 'index')->name('flash');
            Route::post(FlashDeal::LIST[URI], 'add');
            Route::get(FlashDeal::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
            Route::post(FlashDeal::UPDATE[URI] . '/{id}', 'update')->name('update-data');
            Route::post(FlashDeal::STATUS[URI], 'updateStatus')->name('status-update');
            Route::post(FlashDeal::DELETE[URI], 'delete')->name('delete-product');
            Route::get(FlashDeal::ADD_PRODUCT[URI] . '/{deal_id}', 'getAddProductView')->name('add-product');
            Route::post(FlashDeal::ADD_PRODUCT[URI] . '/{deal_id}', 'addProduct');
            Route::any(FlashDeal::SEARCH[URI], 'search')->name('search-product');
        });

        Route::controller(DealOfTheDayController::class)->group(function () {
            Route::get(DealOfTheDay::LIST[URI], 'index')->name('day');
            Route::post(DealOfTheDay::LIST[URI], 'add');
            Route::post(DealOfTheDay::STATUS[URI], 'updateStatus')->name('day-status-update');
            Route::get(DealOfTheDay::UPDATE[URI] . '/{id}', 'getUpdateView')->name('day-update');
            Route::post(DealOfTheDay::UPDATE[URI] . '/{id}', 'update');
            Route::post(DealOfTheDay::DELETE[URI], 'delete')->name('day-delete');
        });

        Route::controller(FeaturedDealController::class)->group(function () {
            Route::get(FeatureDeal::LIST[URI], 'index')->name('feature');
            Route::get('feature-update/{id}', 'getUpdateView')->name('edit');
            Route::post(FeatureDeal::UPDATE[URI], 'update')->name('featured-update');
            Route::post(FeatureDeal::STATUS[URI], 'updateStatus')->name('feature-status');
        });

        Route::group(['prefix' => 'clearance-sale', 'as' => 'clearance-sale.'], function () {
            Route::controller(ClearanceSaleController::class)->group(function () {
                Route::get(ClearanceSale::LIST[URI], 'index')->name('index');
                Route::post(ClearanceSale::STATUS[URI], 'updateStatus')->name('status-update');
                Route::post(ClearanceSale::UPDATE_CONFIG[URI], 'updateClearanceConfig')->name('update-config');
                Route::get(ClearanceSale::SEARCH[URI], 'getSearchedProductsView')->name('search-product-for-clearance');
                Route::get(ClearanceSale::MULTIPLE_PRODUCT_DETAILS[URI], 'getMultipleProductDetailsView')->name('multiple-clearance-product-details');
                Route::post(ClearanceSale::ADD_PRODUCT[URI], 'addClearanceProduct')->name('add-product');
                Route::post(ClearanceSale::PRODUCT_STATUS[URI], 'updateProductStatus')->name('product-status-update');
                Route::delete(ClearanceSale::CLEARANCE_DELETE[URI] . '/{product_id}', 'deleteClearanceProduct')->name('clearance-delete');
                Route::delete(ClearanceSale::CLEARANCE_PRODUCTS_DELETE[URI], 'deleteClearanceAllProduct')->name('clearance-delete-all-product');
                Route::post(ClearanceSale::UPDATE_DISCOUNT[URI], 'updateDiscountAmount')->name('update-discount');
            });

            Route::controller(ClearanceSaleVendorOfferController::class)->group(function () {
                Route::get(ClearanceSale::VENDOR_OFFERS[URI], 'index')->name('vendor-offers');
                Route::get(ClearanceSale::VENDOR_SEARCH[URI], 'getSearchedVendorsView')->name('search-vendor-for-clearance');
                Route::post(ClearanceSale::ADD_VENDOR[URI], 'addClearanceVendorProduct')->name('vendor-add');
                Route::post(ClearanceSale::UPDATE_STATUS[URI], 'updateVendorStatus')->name('update-vendor-status');
                Route::post(ClearanceSale::UPDATE_OFFER_STATUS[URI], 'updateVendorOfferStatus')->name('update-vendor-offer-status');
                Route::delete(ClearanceSale::VENDOR_DELETE[URI] . '/{id}', 'deleteVendorOffer')->name('vendor-delete');
            });

            Route::controller(ClearanceSalePrioritySetupController::class)->group(function () {
                Route::get(ClearanceSale::PRIORITY_SETUP[URI], 'index')->name('priority-setup');
                Route::post(ClearanceSale::PRIORITY_CONFIG[URI], 'updateConfig')->name('priority-setup-config');
            });
        });
    });

    /** Notification and push notification */
    Route::group(['prefix' => 'push-notification', 'as' => 'push-notification.', 'middleware' => ['module:promotion_management']], function () {
        Route::controller(PushNotificationSettingsController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::post('update', 'updatePushNotificationMessage')->name('update');
        });
    });

    Route::group(['prefix' => 'notification', 'as' => 'notification.', 'middleware' => ['module:promotion_management']], function () {
        Route::controller(NotificationController::class)->group(function () {
            Route::get(Notification::INDEX[URI], 'index')->name('index');
            Route::post(Notification::INDEX[URI], 'add');
            Route::get(Notification::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
            Route::post(Notification::UPDATE[URI] . '/{id}', 'update');
            Route::post(Notification::DELETE[URI], 'delete')->name('delete');
            Route::post(Notification::UPDATE_STATUS[URI], 'updateStatus')->name('update-status');
            Route::post(Notification::RESEND_NOTIFICATION[URI], 'resendNotification')->name('resend-notification');
        });
    });

    Route::group(['prefix' => 'support-ticket', 'as' => 'support-ticket.', 'middleware' => ['module:support_section']], function () {
        Route::controller(SupportTicketController::class)->group(function () {
            Route::get('view', 'index')->name('view');
            Route::post('status', 'updateStatus')->name('status');
            Route::get('single-ticket/{id}', 'getView')->name('singleTicket');
            Route::post('single-ticket/{id}', 'reply')->name('replay');
        });
    });

    Route::group(['prefix' => 'messages', 'as' => 'messages.'], function () {
        Route::controller(ChattingController::class)->group(function () {
            Route::get(Chatting::INDEX[URI] . '/{type}', 'index')->name('index');
            Route::get(Chatting::MESSAGE[URI], 'getMessageByUser')->name('message');
            Route::post(Chatting::MESSAGE[URI], 'addAdminMessage');
            Route::get(Chatting::NEW_NOTIFICATION[URI], 'getNewNotification')->name('new-notification');
        });
    });

    Route::group(['prefix' => 'contact', 'as' => 'contact.', 'middleware' => ['module:support_section']], function () {
        Route::controller(ContactController::class)->group(function () {
            Route::get(Contact::LIST[URI], 'index')->name('list');
            Route::get(Contact::VIEW[URI] . '/{id}', 'getView')->name('view');
            Route::post(Contact::FILTER[URI], 'getListByFilter')->name('filter');
            Route::post(Contact::DELETE[URI], 'delete')->name('delete');
            Route::post(Contact::UPDATE[URI] . '/{id}', 'update')->name('update');
            Route::post(Contact::ADD[URI], 'add')->name('store');
            Route::post(Contact::SEND_MAIL[URI] . '/{id}', 'sendMail')->name('send-mail');
        });
    });

    Route::group(['prefix' => 'delivery-man', 'as' => 'delivery-man.', 'middleware' => ['module:user_section']], function () {
        Route::controller(DeliveryManController::class)->group(function () {
            Route::get(DeliveryMan::LIST[URI], 'index')->name('list');
            Route::get(DeliveryMan::ADD[URI], 'getAddView')->name('add');
            Route::post(DeliveryMan::ADD[URI], 'add');
            Route::post(DeliveryMan::STATUS[URI], 'updateStatus')->name('status-update');
            Route::get(DeliveryMan::EXPORT[URI], 'exportList')->name('export');
            Route::get(DeliveryMan::UPDATE[URI] . '/{id}', 'getUpdateView')->name('edit');
            Route::post(DeliveryMan::UPDATE[URI] . '/{id}', 'update')->name('update');
            Route::delete(DeliveryMan::DELETE[URI] . '/{id}', 'delete')->name('delete');
            Route::get(DeliveryMan::EARNING_STATEMENT_OVERVIEW[URI] . '/{id}', 'getEarningOverview')->name('earning-statement-overview');
            Route::get(DeliveryMan::EARNING_OVERVIEW[URI] . '/{id}', 'getOrderWiseEarningView')->name('order-wise-earning');
            Route::post(DeliveryMan::ORDER_WISE_EARNING_LIST_BY_FILTER[URI] . '/{id}', 'getOrderWiseEarningListByFilter')->name('order-wise-earning-list-by-filter');
            Route::get(DeliveryMan::ORDER_HISTORY_LOG[URI] . '/{id}', 'getOrderHistoryList')->name('order-history-log');
            Route::get(DeliveryMan::ORDER_HISTORY_LOG_EXPORT[URI] . '/{id}', 'getOrderHistoryListExport')->name('order-history-log-export');
            Route::get('rating/{id}', 'getRatingView')->name('rating');
            Route::get(DeliveryMan::ORDER_HISTORY[URI] . '/{order}', 'getOrderStatusHistory')->name('ajax-order-status-history');
        });

        Route::controller(DeliveryManCashCollectController::class)->group(function () {
            Route::get(DeliveryManCash::LIST[URI] . '/{id}', 'index')->name('collect-cash');
            Route::post(DeliveryManCash::ADD[URI] . '/{id}', 'getCashReceive')->name('cash-receive');
        });

        Route::controller(DeliverymanWithdrawController::class)->group(function () {
            Route::get(DeliverymanWithdraw::LIST[URI], 'index')->name('withdraw-list');
            Route::post(DeliveryManWithdraw::LIST[URI], 'getFiltered');
            Route::get(DeliverymanWithdraw::EXPORT_LIST[URI], 'exportList')->name('withdraw-list-export');
            Route::get(DeliverymanWithdraw::VIEW[URI] . '/{withdraw_id}', 'getView')->name('withdraw-view');
            Route::post(DeliverymanWithdraw::UPDATE[URI] . '/{id}', 'updateStatus')->name('withdraw-update-status');
        });

        Route::group(['prefix' => 'emergency-contact', 'as' => 'emergency-contact.'], function () {
            Route::controller(EmergencyContactController::class)->group(function () {
                Route::get(EmergencyContact::LIST[URI], 'index')->name('index');
                Route::post(EmergencyContact::ADD[URI], 'add')->name('add');
                Route::get(EmergencyContact::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
                Route::post(EmergencyContact::UPDATE[URI] . '/{id}', 'update');
                Route::post(EmergencyContact::STATUS[URI], 'updateStatus')->name('ajax-status-change');
                Route::delete(EmergencyContact::DELETE[URI], 'delete')->name('destroy');
            });
        });

    });

    Route::group(['prefix' => 'most-demanded', 'as' => 'most-demanded.', 'middleware' => ['module:promotion_management']], function () {
        Route::controller(MostDemandedController::class)->group(function () {
            Route::get('/', 'getListView')->name('index');
            Route::post('store', 'add')->name('store');
            Route::get('update/{id}', 'getUpdateView')->name('edit');
            Route::post('update/{id}', 'update')->name('update');
            Route::post('delete', 'delete')->name('delete');
            Route::post('status', 'updateStatus')->name('status-update');
        });
    });

    Route::group(['prefix' => 'system-setup', 'as' => 'system-setup.'], function () {

        Route::group(['middleware' => ['module:themes_and_addons']], function () {
            Route::group(['prefix' => 'theme', 'as' => 'theme.'], function () {
                Route::controller(ThemeController::class)->group(function () {
                    Route::get('setup', 'index')->name('setup');
                    Route::post('install', 'upload')->name('install');
                    Route::post('activation', 'activation')->name('activation');
                    Route::post('publish', 'publish')->name('publish');
                    Route::post('delete', 'delete')->name('delete');
                    Route::post('notify-all-the-vendors', 'notifyAllTheVendors')->name('notify-all-the-vendors');
                });
            });

            Route::group(['prefix' => 'addon', 'as' => 'addon.'], function () {
                Route::controller(AddonController::class)->group(function () {
                    Route::get('', 'index')->name('index');
                    Route::post('publish', 'publish')->name('publish');
                    Route::post('activation', 'activation')->name('activation');
                    Route::post('upload', 'upload')->name('upload');
                    Route::post('delete', 'delete')->name('delete');
                });
            });
        });

        Route::group(['middleware' => ['module:system_settings']], function () {

            Route::controller(EnvironmentSettingsController::class)->group(function () {
                Route::get('environment-setup', 'index')->name('environment-setup');
                Route::post('environment-setup', 'update');
                Route::post('environment-update-force-https', 'updateForceHttps')->name('environment-https-setup');
                Route::post('optimize-system', 'optimizeSystem')->name('optimize-system');
                Route::post('install-passport', 'installPassport')->name('install-passport');
            });

            Route::controller(BusinessSettingsController::class)->group(function () {
                Route::get('app-settings', 'getAppSettingsView')->name('app-settings');
                Route::post('app-settings', 'updateAppSettings');
            });

            Route::controller(SoftwareUpdateController::class)->group(function () {
                Route::get('software-update', 'index')->name('software-update');
                Route::post('software-update', 'update');
            });

            Route::group(['prefix' => 'language', 'as' => 'language.', 'middleware' => ['module:system_settings']], function () {
                Route::controller(LanguageController::class)->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('add', 'add')->name('add-new');
                    Route::post('update-status', 'updateStatus')->name('update-status');
                    Route::post('update-default-status', 'updateDefaultStatus')->name('update-default-status');
                    Route::post('update', 'update')->name('update');
                    Route::get('delete/{lang}', 'delete')->name('delete');
                    Route::get('translate/{lang}', 'getTranslateView')->name('translate');
                    Route::get('translate-list/{lang}', 'getTranslateList')->name('translate.list');
                    Route::post('translate-submit/{lang}', 'updateTranslate')->name('translate-submit');
                    Route::post('remove-key/{lang}', 'deleteTranslateKey')->name('remove-key');
                    Route::any('auto-translate/{lang}', 'getAutoTranslate')->name('auto-translate');
                    Route::any('auto-translate-all/{lang}', 'getAutoTranslateAllMessages')->name('auto-translate-all');
                });
            });

            Route::group(['prefix' => 'currency', 'as' => 'currency.', 'middleware' => ['module:system_settings']], function () {
                Route::controller(CurrencyController::class)->group(function () {
                    Route::get('view', 'index')->name('view')->middleware('actch');
                    Route::post('store', 'add')->name('store');
                    Route::post('update', 'update')->name('update');
                    Route::post('delete', 'delete')->name('delete');
                    Route::post('status', 'status')->name('status');
                    Route::post('check-currency-update', 'checkSystemCurrency')->name('check-currency-update');
                    Route::post('system-currency-update', 'updateSystemCurrency')->name('system-currency-update');
                });
            });

            Route::controller(BusinessSettingsController::class)->group(function () {
                Route::get('cookie-settings', 'getCookieSettingsView')->name('cookie-settings');
                Route::post('cookie-settings', 'updateCookieSetting');
            });

            Route::controller(DatabaseSettingController::class)->group(function () {
                Route::get('db-index', 'index')->name('db-index');
                Route::post('db-clean', 'delete')->name('clean-db');
            });

            Route::group(['prefix' => 'login-settings', 'as' => 'login-settings.'], function () {
                Route::controller(SystemLoginSetupController::class)->group(function () {
                    Route::get('customer-login-setup', 'getCustomerLoginSetupView')->name('customer-login-setup');
                    Route::post('customer-login-setup', 'updateCustomerLoginSetup');
                    Route::post('customer-config-validation', 'getConfigValidation')->name('config-status-validation');

                    Route::get('otp-setup', 'getOtpSetupView')->name('otp-setup');
                    Route::post('otp-setup', 'updateOtpSetup');

                    Route::get('login-url-setup', 'getLoginSetupView')->name('login-url-setup');
                    Route::post('login-url-setup', 'updateLoginSetupView');
                });
            });

            Route::group(['prefix' => 'email-templates', 'as' => 'email-templates.', 'middleware' => ['module:system_settings']], function () {
                Route::controller(EmailTemplatesController::class)->group(function () {
                    Route::get('index', 'index')->name('index');
                    Route::get('/' . '/{type}' . '/{tab}', 'getView')->name('view');
                    Route::post(EmailTemplate::UPDATE[URI] . '/{type}' . '/{tab}', 'update')->name('update');
                    Route::post(EmailTemplate::UPDATE_STATUS[URI] . '/{type}' . '/{tab}', 'updateStatus')->name('update-status');
                });
            });

            Route::group(['prefix' => 'file-manager', 'as' => 'file-manager.', 'middleware' => ['module:system_settings']], function () {
                Route::controller(FileManagerController::class)->group(function () {
                    Route::get('index', 'getFoldersView')->name('index');
                    Route::get('download/{file_name}', 'download')->name('download');
                    Route::post('image-upload', 'upload')->name('image-upload');
                });
            });
        });
    });

    Route::group(['prefix' => 'third-party', 'as' => 'third-party.'], function () {
        Route::group(['middleware' => ['module:3rd_party_setup']], function () {
            Route::group(['prefix' => 'payment-method', 'as' => 'payment-method.'], function () {
                Route::controller(PaymentMethodController::class)->group(function () {
                    Route::get('/', 'index')->name('index')->middleware('actch');
                    Route::put('addon-payment-set', 'UpdatePaymentConfig')->name('addon-payment-set');
                    Route::post('payment-status', 'UpdateStatus')->name('payment-status');
                });
            });

            Route::group(['prefix' => 'offline-payment-method', 'as' => 'offline-payment-method.'], function () {
                Route::controller(OfflinePaymentMethodController::class)->group(function () {
                    Route::get('index', 'index')->name('index')->middleware('actch');
                    Route::get('add', 'getAddView')->name('add')->middleware('actch');
                    Route::post('add', 'add')->middleware('actch');
                    Route::get('update/{id}', 'getUpdateView')->name('update')->middleware('actch');
                    Route::post('update/{id}', 'update')->middleware('actch');
                    Route::post('delete', 'delete')->name('delete')->middleware('actch');
                    Route::post('update-status', 'updateStatus')->name('update-status')->middleware('actch');
                });
            });

            Route::group(['prefix' => 'firebase-configuration', 'as' => 'firebase-configuration.'], function () {
                Route::controller(PushNotificationSettingsController::class)->group(function () {
                    Route::get('setup', 'getFirebaseConfigurationView')->name('setup');
                    Route::post('setup', 'getFirebaseConfigurationUpdate');
                });
                Route::controller(FirebaseOTPVerificationController::class)->group(function () {
                    Route::get('authentication', 'index')->name('authentication');
                    Route::post('update', 'updateAuthentication')->name('update');
                    Route::post('firebase-config-validation', 'getConfigValidation')->name('config-status-validation');
                });
            });

            Route::controller(BusinessSettingsController::class)->group(function () {
                Route::get('analytics-index', 'getAnalyticsView')->name('analytics-index');
                Route::post('analytics-update', 'updateAnalytics')->name('analytics-update');
            });

            Route::group(['prefix' => 'social-login', 'as' => 'social-login.'], function () {
                Route::controller(SocialLoginSettingsController::class)->group(function () {
                    Route::get('view', 'index')->name('view');
                    Route::post('update/{service}', 'update')->name('update');
                    Route::post('update-apple/{service}', 'updateAppleLogin')->name('update-apple');
                });
            });

            Route::group(['prefix' => 'storage-connection-settings', 'as' => 'storage-connection-settings.'], function () {
                Route::controller(StorageConnectionSettingsController::class)->group(function () {
                    Route::get('index', 'index')->name('index');
                    Route::post('update-storage-type', 'updateStorageType')->name('update-storage-type');
                    Route::post('s3-credential', 'updateS3Credential')->name('s3-credential');
                });
            });

            Route::group(['prefix' => 'social-media-chat', 'as' => 'social-media-chat.'], function () {
                Route::controller(SocialMediaChatController::class)->group(function () {
                    Route::get('view', 'index')->name('view');
                    Route::post('update/{service}', 'update')->name('update');
                });
            });

            Route::group(['prefix' => 'mail', 'as' => 'mail.'], function () {
                Route::controller(MailController::class)->group(function () {
                    Route::get(Mail::VIEW[URI], 'index')->name('index');
                    Route::post(Mail::UPDATE[URI], 'update')->name('update');
                    Route::post(Mail::UPDATE_SENDGRID[URI], 'updateSendGrid')->name('update-sendgrid');
                    Route::post(Mail::SEND[URI], 'send')->name('send');
                });
            });

            Route::controller(SMSModuleController::class)->group(function () {
                Route::get('sms-module', 'index')->name('sms-module');
                Route::put('addon-sms-set', 'update')->name('addon-sms-set');
                Route::post('send-test-sms', 'sendSMS')->name('send-test-sms');
            });

            Route::controller(RecaptchaController::class)->group(function () {
                Route::get('recaptcha', 'index')->name('captcha');
                Route::post('recaptcha', 'update');
            });

            Route::controller(GoogleMapAPIController::class)->group(function () {
                Route::get('map-api', 'index')->name('map-api');
                Route::post('map-api', 'update');
            });
        });
    });

    Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.'], function () {

        Route::group(['middleware' => ['module:business_settings']], function () {
            Route::group(['prefix' => 'web-config', 'as' => 'web-config.'], function () {
                Route::controller(BusinessSettingsController::class)->group(function () {
                    Route::get('/', 'index')->name('index')->middleware('actch');
                    Route::post('/', 'updateSettings')->name('update');
                });
            });

            Route::controller(BusinessSettingsController::class)->group(function () {
                Route::post('maintenance-mode', 'updateSystemMode')->name('maintenance-mode');
            });


            Route::controller(WebsiteSetupController::class)->group(function () {
                Route::get('website-setup', 'getView')->name('website-setup')->middleware('actch');
                Route::post('website-setup', 'updateWebsiteSetup');
            });

            Route::group(['prefix' => 'vendor-settings', 'as' => 'vendor-settings.'], function () {
                Route::controller(VendorSettingsController::class)->group(function () {
                    Route::get('', 'index')->name('index')->middleware('actch');
                    Route::post('update-vendor-settings', 'update')->name('update-vendor-settings');
                });
            });

            Route::group(['prefix' => 'product-settings', 'as' => 'product-settings.'], function () {
                Route::controller(BusinessSettingsController::class)->group(function () {
                    Route::get('/', 'getProductSettingsView')->name('index');
                    Route::post('/', 'updateProductSettings');
                });
            });

            Route::group(['prefix' => 'delivery-man-settings', 'as' => 'delivery-man-settings.'], function () {
                Route::controller(DeliverymanSettingsController::class)->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('delivery-man-settings/update', 'update')->name('update');
                    Route::post('delivery-man-settings/upload-picture', 'uploadPicture')->name('upload-picture');
                });
            });

            Route::controller(CustomerController::class)->group(function () {
                Route::get('customer-settings', 'getCustomerSettingsView')->name('customer-settings');
                Route::post('customer-settings', 'update');
            });

            Route::group(['prefix' => 'order-settings', 'as' => 'order-settings.'], function () {
                Route::controller(OrderSettingsController::class)->group(function () {
                    Route::get('index', 'index')->name('index');
                    Route::post('update-order-settings', 'update')->name('update-order-settings');
                });
            });

            Route::controller(BusinessSettingsController::class)->group(function () {
                Route::get('refund-setup', 'getRefundSetupView')->name('refund-setup')->middleware('actch');
                Route::post('refund-setup', 'updateRefundSetup')->name('refund-setup-update');
            });

            Route::group(['prefix' => 'shipping-method', 'as' => 'shipping-method.'], function () {
                Route::controller(ShippingMethodController::class)->group(function () {
                    Route::get('index', 'index')->name('index');
                    Route::post('index', 'add');
                    Route::get('update' . '/{id}', 'getUpdateView')->name('update');
                    Route::post('update' . '/{id}', 'update');
                    Route::post('update-status', 'updateStatus')->name('update-status');
                    Route::post('delete', 'delete')->name('delete');
                    Route::post('update-shipping-responsibility', 'updateShippingResponsibility')->name('update-shipping-responsibility');
                });
            });

            Route::group(['prefix' => 'shipping-type', 'as' => 'shipping-type.'], function () {
                Route::post(ShippingType::INDEX[URI], [ShippingTypeController::class, 'addOrUpdate'])->name('index');
            });

            Route::group(['prefix' => 'category-shipping-cost', 'as' => 'category-shipping-cost.'], function () {
                Route::controller(CategoryShippingCostController::class)->group(function () {
                    Route::post('store', 'add')->name('store');
                });
            });

            Route::group(['prefix' => 'payment-method', 'as' => 'payment-method.'], function () {
                Route::controller(PaymentMethodController::class)->group(function () {
                    Route::get('payment-option', 'getPaymentOptionView')->name('payment-option');
                    Route::post('payment-option', 'updatePaymentOption');
                });
            });

            Route::group(['prefix' => 'delivery-restriction', 'as' => 'delivery-restriction.'], function () {
                Route::controller(DeliveryRestrictionController::class)->group(function () {
                    Route::get('', 'index')->name('index');
                    Route::post('add-delivery-country', 'add')->name('add-delivery-country');
                    Route::delete('delivery-country-delete', 'delete')->name('delivery-country-delete');
                    Route::post('add-zip-code', 'addZipCode')->name('add-zip-code');
                    Route::delete('zip-code-delete', 'deleteZipCode')->name('zip-code-delete');
                    Route::post('country-restriction-status-change', 'countryRestrictionStatusChange')->name('country-restriction-status-change');
                    Route::post("zipcode-restriction-status-change", 'zipcodeRestrictionStatusChange')->name('zipcode-restriction-status-change');
                });
            });

            Route::group(['prefix' => 'invoice-settings', 'as' => 'invoice-settings.'], function () {
                Route::controller(InvoiceSettingsController::class)->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/', 'update')->name('update');
                });
            });
        });

    });

    Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.'], function () {

        Route::group(['middleware' => ['module:business_settings']], function () {
            Route::controller(InhouseShopController::class)->group(function () {
                Route::get('inhouse-shop', 'index')->name('inhouse-shop');
                Route::post('inhouse-shop', 'update');
                Route::post('inhouse-shop-temporary-close', 'getTemporaryClose')->name('inhouse-shop-temporary-close');
                Route::post('vacation-update', 'updateVacation')->name('inhouse-shop-vacation-update');

                Route::get('inhouse-shop/setup', 'getSetupView')->name('inhouse-shop-setup');
                Route::post('inhouse-shop/setup', 'updateSetup');
            });

        });

        Route::group(['prefix' => 'priority-setup', 'as' => 'priority-setup.'], function () {
            Route::controller(PrioritySetupController::class)->group(function () {
                Route::get('', 'index')->name('index');
                Route::post('', 'update');
            });
        });
    });

    Route::group(['prefix' => 'seo-settings', 'as' => 'seo-settings.'], function () {
        Route::group(['middleware' => ['module:business_settings']], function () {
            Route::controller(SEOSettingsController::class)->group(function () {
                Route::get('web-master-tool', 'index')->name('web-master-tool');
                Route::post('web-master-tool', 'updateWebMasterTool');
                Route::get('robot-txt', 'getRobotTxtView')->name('robot-txt');
                Route::post('robot-text', 'updateRobotText')->name('update-robot-text');
            });

            Route::group(['prefix' => 'robots-meta-content', 'as' => 'robots-meta-content.'], function () {
                Route::controller(RobotsMetaContentController::class)->group(function () {
                    Route::get(RobotsMetaContent::ROBOTS_META_CONTENT[URI], 'index')->name('index');
                    Route::post(RobotsMetaContent::ADD_PAGE[URI], 'addPage')->name('add-page');
                    Route::get(RobotsMetaContent::DELETE_PAGE[URI], 'getPageDelete')->name('delete-page');
                    Route::get(RobotsMetaContent::PAGE_CONTENT_VIEW[URI], 'getPageAddContentView')->name('page-content-view');
                    Route::post(RobotsMetaContent::PAGE_CONTENT_UPDATE[URI], 'getPageContentUpdate')->name('page-content-update');
                });
            });

            Route::controller(SiteMapController::class)->group(function () {
                Route::get(SiteMap::SITEMAP[URI], 'index')->name('sitemap');
                Route::get(SiteMap::GENERATE_AND_DOWNLOAD[URI], 'getGenerateAndDownload')->name('sitemap-generate-download');
                Route::get(SiteMap::GENERATE_AND_UPLOAD[URI], 'getGenerateAndUpload')->name('sitemap-generate-upload');
                Route::post(SiteMap::UPLOAD[URI], 'getUpload')->name('sitemap-manual-upload');
                Route::get(SiteMap::DOWNLOAD[URI], 'getDownload')->name('sitemap-download');
                Route::get(SiteMap::DELETE[URI], 'getDelete')->name('sitemap-delete');
            });

            Route::group(['prefix' => 'error-logs', 'as' => 'error-logs.'], function () {
                Route::controller(ErrorLogsController::class)->group(function () {
                    Route::get('index', 'index')->name('index');
                    Route::post('index', 'update');
                    Route::delete('index', 'delete');
                    Route::delete('delete-selected-error-logs', 'deleteSelectedErrorLogs')->name('delete-selected-error-logs');
                });
            });
        });
    });

    Route::group(['prefix' => 'pages-and-media', 'as' => 'pages-and-media.'], function () {
        Route::group(['middleware' => ['module:business_settings']], function () {
            Route::controller(PagesController::class)->group(function () {
                Route::get('list', 'index')->name('list');
                Route::get('add', 'getAddView')->name('add');
                Route::post('add', 'getAdd');
                Route::get('update', 'getUpdateView')->name('update');
                Route::post('update', 'getUpdate');
                Route::post('delete', 'getDelete')->name('delete');
                Route::post('update-status', 'updateStatus')->name('update-status');
            });

            Route::controller(FeaturesSectionController::class)->group(function () {
                Route::get('company-reliability', 'getCompanyReliabilityView')->name('company-reliability');
                Route::post('company-reliability', 'updateCompanyReliability');
            });

            Route::controller(SocialMediaSettingsController::class)->group(function () {
                Route::get('social-media', 'index')->name('social-media');
                Route::get('fetch', 'getList')->name('fetch');
                Route::post('social-media-store', 'add')->name('social-media-store');
                Route::post('social-media-edit', 'getUpdate')->name('social-media-edit');
                Route::post('social-media-update', 'update')->name('social-media-update');
                Route::post('social-media-delete', 'delete')->name('social-media-delete');
                Route::post('social-media-status-update', 'updateStatus')->name('social-media-status-update');
            });

            Route::group(['prefix' => 'vendor-registration-settings', 'as' => 'vendor-registration-settings.'], function () {
                Route::controller(VendorRegistrationSettingController::class)->group(function () {
                    Route::get(VendorRegistrationSetting::INDEX[URI], 'index')->name('index');
                    Route::post(VendorRegistrationSetting::INDEX[URI], 'updateHeaderSection');
                    Route::get(VendorRegistrationSetting::WITH_US[URI], 'getSellWithUsView')->name('with-us');
                    Route::post(VendorRegistrationSetting::WITH_US[URI], 'updateSellWithUsSection');
                    Route::get(VendorRegistrationSetting::BUSINESS_PROCESS[URI], 'getBusinessProcessView')->name('business-process');
                    Route::post(VendorRegistrationSetting::BUSINESS_PROCESS[URI], 'updateBusinessProcess');
                    Route::get(VendorRegistrationSetting::DOWNLOAD_APP[URI], 'getDownloadAppView')->name('download-app');
                    Route::post(VendorRegistrationSetting::DOWNLOAD_APP[URI], 'updateDownloadAppSection');
                    Route::get(VendorRegistrationSetting::FAQ[URI], 'getFAQView')->name('faq');
                });
            });

            Route::group(['prefix' => 'vendor-registration-reason', 'as' => 'vendor-registration-reason.'], function () {
                Route::controller(VendorRegistrationReasonController::class)->group(function () {
                    Route::post(VendorRegistrationReason::ADD[URI], 'add')->name('add');
                    Route::get(VendorRegistrationReason::UPDATE[URI], 'getUpdateView')->name('update');
                    Route::post(VendorRegistrationReason::UPDATE[URI], 'update');
                    Route::post(VendorRegistrationReason::UPDATE_STATUS[URI], 'updateStatus')->name('update-status');
                    Route::post(VendorRegistrationReason::DELETE[URI], 'delete')->name('delete');
                });
            });

            Route::controller(FeaturesSectionController::class)->group(function () {
                Route::get('features-section', 'index')->name('features-section');
                Route::post('features-section/submit', 'update')->name('features-section.submit');
                Route::post('features-section/icon-remove', 'delete')->name('features-section.icon-remove');
            });
        });
    });

    Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.', 'middleware' => ['module:promotion_management']], function () {
        Route::controller(BusinessSettingsController::class)->group(function () {
            Route::get('announcement', 'getAnnouncementView')->name('announcement');
            Route::post('announcement', 'updateAnnouncement');
        });
    });

    Route::group(['prefix' => 'helpTopic', 'as' => 'helpTopic.', 'middleware' => ['module:business_settings']], function () {
        Route::controller(HelpTopicController::class)->group(function () {
            Route::get('index', 'index')->name('list');
            Route::post('add-new', 'add')->name('add-new');
            Route::post(HelpTopic::STATUS[URI] . '/{id}', 'updateStatus')->name('status');
            Route::get(HelpTopic::UPDATE[URI] . '/{id}', 'getUpdateResponse')->name('update');
            Route::post('feature-status-update', 'updateFeatureStatus')->name('feature-status-update');
            Route::post('update' . '/{id}', 'update');
            Route::post('delete', 'delete')->name('delete');
        });
    });

    Route::group(['prefix' => 'refund-section', 'as' => 'refund-section.', 'middleware' => ['module:order_management']], function () {
        Route::group(['prefix' => 'refund', 'as' => 'refund.'], function () {
            Route::controller(RefundController::class)->group(function () {
                Route::get('list/{status}', 'index')->name('list');
                Route::get('export/{status}', 'exportList')->name('export');
                Route::get('details/{id}', 'getDetailsView')->name('details');
                Route::post('refund-status-update', 'updateRefundStatus')->name('refund-status-update');
            });
        });
    });

});
