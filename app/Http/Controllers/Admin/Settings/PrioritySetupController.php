<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Enums\ViewPaths\Admin\PrioritySetup;
use App\Http\Controllers\BaseController;
use App\Services\PrioritySetupService;
use App\Traits\CacheManagerTrait;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PrioritySetupController extends BaseController
{
    use CacheManagerTrait;

    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly PrioritySetupService               $prioritySetupService,

    )
    {
    }

    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getView();
    }

    public function getView(): View
    {
        $brandPriority = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'brand_list_priority'])['value']);
        $categoryPriority = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'category_list_priority'])['value']);
        $vendorProductListPriority = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'vendor_product_list_priority'])['value']);
        $featureProductPriority = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'featured_product_priority'])['value']);
        $newArrivalProductListPriority = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'new_arrival_product_list_priority'])['value']);
        $categoryWiseProductListPriority = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'category_wise_product_list_priority'])['value']);
        $bestSellingProductListPriority = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'best_selling_product_list_priority'])['value']);
        $topRatedProductListPriority = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'top_rated_product_list_priority'])['value']);
        $searchedProductListPriority = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'searched_product_list_priority'])['value']);
        $topVendorPriority = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'top_vendor_list_priority'])['value']);
        $vendorListPriority = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'vendor_list_priority'])['value']);
        return view('admin-views.business-settings.priority-setup.index', compact('featureProductPriority', 'topVendorPriority', 'brandPriority', 'categoryPriority', 'vendorListPriority', 'searchedProductListPriority', 'vendorProductListPriority', 'bestSellingProductListPriority', 'topRatedProductListPriority', 'categoryWiseProductListPriority', 'newArrivalProductListPriority'));
    }

    public function update(Request $request): RedirectResponse
    {
        return match ($request['type']) {
            'vendor_product_list' => $this->updateVendorProductListPriority(request: $request),
            'featured_product' => $this->updateFeaturedProductPriority(request: $request),
            'new_arrival_product_list' => $this->updateNewArrivalProductListPriority(request: $request),
            'searched_product_list' => $this->updateSearchedProductPriority(request: $request),
            'category_wise_product_list' => $this->updateCategoryWiseProductListPriority(request: $request),
            'best_selling_product_list' => $this->updateBestSellingProductListPriority(request: $request),
            'top_rated_product_list' => $this->updateTopRatedProductListPriority(request: $request),
            'feature_deal' => $this->updateFeatureDealPriority(request: $request),
            'flash_deal' => $this->updateFlashDealPriority(request: $request),
            'top_vendor' => $this->updateTopVendorPriority(request: $request),
            'vendor_list' => $this->updateVendorListPriority(request: $request),
            'brand' => $this->updateBrandPriority(request: $request),
            'category' => $this->updateCategoryPriority(request: $request),
        };
    }

    protected function updateVendorProductListPriority($request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'vendor_product_list_priority',
            value: $this->prioritySetupService->updateVendorProductListPrioritySetupData(request: $request));
        ToastMagic::success(translate('vendor_product_list_priority_setup_updated_successfully'));
        return redirect()->back();
    }

    protected function updateFeaturedProductPriority($request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'featured_product_priority',
            value: $this->prioritySetupService->updateFeaturedProductPrioritySetupData(request: $request));
        ToastMagic::success(translate('featured_product_priority_setup_updated_successfully'));
        return redirect()->back();
    }

    protected function updateNewArrivalProductListPriority($request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'new_arrival_product_list_priority',
            value: $this->prioritySetupService->updateNewArrivalProductListPrioritySetupData(request: $request));
        ToastMagic::success(translate('new_arrival_product_list_priority_setup_updated_successfully'));
        return redirect()->back();
    }

    protected function updateTopRatedProductListPriority($request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'top_rated_product_list_priority',
            value: $this->prioritySetupService->updateTopRatedProductListPrioritySetupData(request: $request));
        ToastMagic::success(translate('top_rated_product_list_priority_setup_updated_successfully'));
        return redirect()->back();
    }

    protected function updateCategoryWiseProductListPriority($request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'category_wise_product_list_priority',
            value: $this->prioritySetupService->updateCategoryWiseProductListPrioritySetupData(request: $request));
        ToastMagic::success(translate('category_wise_product_list_priority_setup_updated_successfully'));
        return redirect()->back();
    }

    protected function updateBestSellingProductListPriority($request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'best_selling_product_list_priority',
            value: $this->prioritySetupService->updateBestSellingProductListPrioritySetupData(request: $request));
        ToastMagic::success(translate('best_selling_product_list_priority_setup_updated_successfully'));
        return redirect()->back();
    }

    protected function updateSearchedProductPriority($request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'searched_product_list_priority',
            value: $this->prioritySetupService->updateProductListPrioritySetupData(request: $request));
        ToastMagic::success(translate('searched_product_list_priority_setup_updated_successfully'));
        return redirect()->back();
    }

    protected function updateFeatureDealPriority($request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'feature_deal_priority',
            value: $this->prioritySetupService->updateFeatureDealPrioritySetupData(request: $request)
        );
        ToastMagic::success(translate('feature_deal_priority_setup_updated_successfully'));
        return redirect()->back();
    }

    protected function updateFlashDealPriority($request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'flash_deal_priority',
            value: $this->prioritySetupService->updateFlashDealPrioritySetupData(request: $request)
        );
        cacheRemoveByType(type: 'flash_deals');
        ToastMagic::success(translate('flash_deal_priority_setup_updated_successfully'));
        return redirect()->back();
    }

    protected function updateTopVendorPriority($request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'top_vendor_list_priority',
            value: $this->prioritySetupService->updateTopVendorPrioritySetupData(request: $request)
        );
        cacheRemoveByType(type: 'shops');
        ToastMagic::success(translate('top_vendor_list_priority_setup_updated_successfully'));
        return redirect()->back();
    }

    protected function updateVendorListPriority($request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'vendor_list_priority',
            value: $this->prioritySetupService->updateVendorPrioritySetupData(request: $request)
        );
        cacheRemoveByType(type: 'shops');
        ToastMagic::success(translate('vendor_list_priority_setup_updated_successfully'));
        return redirect()->back();
    }

    protected function updateBrandPriority($request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'brand_list_priority',
            value: $this->prioritySetupService->updateBrandAndCategoryPrioritySetupData(request: $request)
        );
        cacheRemoveByType(type: 'brands');
        ToastMagic::success(translate('brand_list_priority_setup_updated_successfully'));
        return redirect()->back();
    }

    protected function updateCategoryPriority($request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'category_list_priority',
            value: $this->prioritySetupService->updateBrandAndCategoryPrioritySetupData(request: $request)
        );
        cacheRemoveByType(type: 'categories');
        ToastMagic::success(translate('category_list_priority_setup_updated_successfully'));
        return redirect()->back();
    }
}
