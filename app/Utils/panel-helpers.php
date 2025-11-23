<?php

use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Product;

if (!function_exists('checkSetupGuideRequirements')) {
    function checkSetupGuideRequirements($panel = 'admin'): array
    {
        $steps = getSetupGuideSteps(panel: $panel);

        uasort($steps, function ($a, $b) {
            return (empty($b['checked']) ? 0 : 1) <=> (empty($a['checked']) ? 0 : 1);
        });

        $checkedCount = count(array_filter($steps, function ($step) {
            return !empty($step['checked']);
        }));

        return [
          'totalSteps' => count($steps) - $checkedCount,
          'completePercent' => $checkedCount > 0 ? floor(($checkedCount / count($steps)) * 100) : 0,
          'steps' => $steps,
        ];
    }
}

if (!function_exists('checkSetupGuideCacheKey')) {
    function checkSetupGuideCacheKey($key = '')
    {
        $checkSetupGuideCacheKeys = getWebConfig(name: 'setup_guide_requirements_for_admin') ?? [
            'general_setup' => 0,
            'shipping_method' => 0,
            'language_setup' => 0,
            'currency_setup' => 0,
            'customer_login' => 0,
            'google_map_apis' => 0,
            'notification_configuration' => 0,
            'digital_payment_setup' => 0,
            'offline_payment_setup' => 0,
            'category_setup' => Category::all()->count() > 0 ? 1 : 0,
            'brand_setup' => Brand::all()->count() > 0 ? 1 : 0,
            'inhouse_shop_setup' => 0,
            'add_new_product' => Product::all()->count() > 0 ? 1 : 0,
        ];

        return $checkSetupGuideCacheKeys[$key] ?? false;
    }
}

if (!function_exists('updateSetupGuideCacheKey')) {
    function updateSetupGuideCacheKey($key = ''): void
    {
        $checkSetupGuideCacheKeys = getWebConfig(name: 'setup_guide_requirements_for_admin') ?? [];
        $checkSetupGuideCacheKeys = empty($checkSetupGuideCacheKeys) ? [] : $checkSetupGuideCacheKeys;

        if (!isset($checkSetupGuideCacheKeys[$key]) || $checkSetupGuideCacheKeys[$key] !== true) {
            $newCacheKeys = [];
            $newCacheKeys[$key] = true;
            $checkSetupGuideCacheKeys = array_merge($checkSetupGuideCacheKeys, $newCacheKeys);
            if (BusinessSetting::where(['type' => 'setup_guide_requirements_for_admin'])->first()) {
                BusinessSetting::where(['type' => 'setup_guide_requirements_for_admin'])->update([
                    'value' => json_encode($checkSetupGuideCacheKeys),
                ]);
            } else {
                BusinessSetting::create([
                    'type' => 'setup_guide_requirements_for_admin',
                    'value' => json_encode($checkSetupGuideCacheKeys),
                    'updated_at' => now()
                ]);
            }
            cacheRemoveByType(type: 'business_settings');
        }
    }
}


if (!function_exists('getSetupGuideSteps')) {
    function getSetupGuideSteps($panel = 'admin'): array
    {
        $steps = [];
        $steps['general_setup'] = [
            'title' => translate('General_Setup'),
            'route' => route('admin.business-settings.web-config.index'),
            'checked' => checkSetupGuideCacheKey(key: 'general_setup'),
            'disabled' => checkSetupGuideCacheKey(key: 'general_setup'),
        ];

        $steps['shipping_method'] = [
            'title' => translate('Shipping_Method'),
            'route' => route('admin.business-settings.shipping-method.index'),
            'checked' => checkSetupGuideCacheKey(key: 'shipping_method'),
            'disabled' => checkSetupGuideCacheKey(key: 'shipping_method'),
        ];

        $steps['language_setup'] = [
            'title' => translate('language_setup'),
            'route' => route('admin.system-setup.language.index'),
            'checked' => checkSetupGuideCacheKey(key: 'language_setup'),
            'disabled' => checkSetupGuideCacheKey(key: 'language_setup'),
        ];

        $steps['currency_setup'] = [
            'title' => translate('Currency'),
            'route' => route('admin.system-setup.currency.view'),
            'checked' => checkSetupGuideCacheKey(key: 'currency_setup'),
            'disabled' => checkSetupGuideCacheKey(key: 'currency_setup'),
        ];

        $steps['customer_login'] = [
            'title' => translate('Customer_Login'),
            'route' => route('admin.system-setup.login-settings.customer-login-setup'),
            'checked' => checkSetupGuideCacheKey(key: 'customer_login'),
            'disabled' => checkSetupGuideCacheKey(key: 'customer_login'),
        ];

        $steps['google_map_apis'] = [
            'title' => translate('Google_Map_APIs'),
            'route' => route('admin.third-party.map-api'),
            'checked' => checkSetupGuideCacheKey(key: 'google_map_apis'),
            'disabled' => checkSetupGuideCacheKey(key: 'google_map_apis'),
        ];

        $steps['notification_configuration'] = [
            'title' => translate('Notification_Configuration'),
            'route' => route('admin.push-notification.index'),
            'checked' => checkSetupGuideCacheKey(key: 'notification_configuration'),
            'disabled' => checkSetupGuideCacheKey(key: 'notification_configuration'),
        ];

        $steps['digital_payment_setup'] = [
            'title' => translate('Digital_Payment_Setup'),
            'route' => route('admin.third-party.payment-method.index'),
            'checked' => checkSetupGuideCacheKey(key: 'digital_payment_setup'),
            'disabled' => checkSetupGuideCacheKey(key: 'digital_payment_setup'),
        ];

        $steps['offline_payment_setup'] = [
            'title' => translate('Offline_Payment_Setup'),
            'route' => route('admin.third-party.offline-payment-method.index'),
            'checked' => checkSetupGuideCacheKey(key: 'offline_payment_setup'),
            'disabled' => checkSetupGuideCacheKey(key: 'offline_payment_setup'),
        ];

        $steps['category_setup'] = [
            'title' => translate('Category_Setup'),
            'route' => route('admin.category.view'),
            'checked' => checkSetupGuideCacheKey(key: 'category_setup'),
            'disabled' => checkSetupGuideCacheKey(key: 'category_setup'),
        ];

        $steps['brand_setup'] = [
            'title' => translate('Brand_Setup'),
            'route' => route('admin.brand.add-new'),
            'checked' => checkSetupGuideCacheKey(key: 'brand_setup'),
            'disabled' => checkSetupGuideCacheKey(key: 'brand_setup'),
        ];

        $steps['inhouse_shop_setup'] = [
            'title' => translate('Inhouse_Shop'),
            'route' => route('admin.business-settings.inhouse-shop'),
            'checked' => checkSetupGuideCacheKey(key: 'inhouse_shop_setup'),
            'disabled' => checkSetupGuideCacheKey(key: 'inhouse_shop_setup'),
        ];

        $steps['add_new_product'] = [
            'title' => translate('Add_New_Product'),
            'route' => route('admin.products.add'),
            'checked' => checkSetupGuideCacheKey(key: 'add_new_product'),
            'disabled' => checkSetupGuideCacheKey(key: 'add_new_product'),
        ];
        return $steps;
    }
}
