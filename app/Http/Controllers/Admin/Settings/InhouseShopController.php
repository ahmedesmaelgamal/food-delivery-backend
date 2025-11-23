<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Contracts\Repositories\AdminRepositoryInterface;
use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Http\Controllers\BaseController;
use App\Traits\FileManagerTrait;
use Carbon\Carbon;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class InhouseShopController extends BaseController
{
    use FileManagerTrait {
        delete as deleteFile;
        update as updateFile;
    }

    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly AdminRepositoryInterface           $adminRepo,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getView(request: $request);
    }

    public function getView(Request $request): View
    {
        $temporaryClose = getWebConfig(name: 'temporary_close');
        $vacation = getWebConfig('vacation_add');
        $admin = $this->adminRepo->getFirstWhere(params: ['id' => 1]);
        $minimumOrderAmountStatus = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'minimum_order_amount_status']);
        $minimumOrderAmount = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'minimum_order_amount']);
        $freeDeliveryStatus = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'free_delivery_status']);
        $freeDeliveryOverAmount = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'free_delivery_over_amount']);

        if ($request->has('action') && $request['action'] == 'edit') {
            return view('admin-views.inhouse-shop.edit', compact('temporaryClose', 'vacation', 'admin', 'minimumOrderAmountStatus', 'minimumOrderAmount', 'freeDeliveryStatus', 'freeDeliveryOverAmount'));
        }
        return view('admin-views.inhouse-shop.index', compact('temporaryClose', 'vacation', 'admin', 'minimumOrderAmountStatus', 'minimumOrderAmount', 'freeDeliveryStatus', 'freeDeliveryOverAmount'));
    }

    public function update(Request $request): RedirectResponse
    {
        $imgBanner = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'shop_banner']);
        if ($request->has('shop_banner')) {
            $imgBannerImage = $imgBanner ? $this->updateFile(dir: 'shop/', oldImage: (is_array($imgBanner['value']) ? $imgBanner['value']['image_name'] : $imgBanner['value'] ), format: 'webp', image: $request->file('shop_banner')):$this->upload('shop/', 'webp',  $request->file('shop_banner'));
            $imgBannerImageArray = [
                'image_name' =>  $imgBannerImage,
                'storage' => config('filesystems.disks.default') ?? 'public'
            ];
            $this->businessSettingRepo->updateOrInsert(type: 'shop_banner', value: json_encode($imgBannerImageArray));
        }
        $bottomBanner = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'bottom_banner']);
        if ($request->has('bottom_banner')) {
            $bottomBannerImage = !empty($bottomBanner) ? $this->updateFile(dir: 'shop/', oldImage: (is_array($bottomBanner['value']) ? $bottomBanner['value']['image_name'] : $bottomBanner['value'] ), format: 'webp', image: $request->file('bottom_banner')) : $this->upload('shop/', 'webp',  $request->file('bottom_banner')) ;
            $bottomBannerImageArray = [
                'image_name' =>  $bottomBannerImage,
                'storage' => config('filesystems.disks.default') ?? 'public'
            ];
            $this->businessSettingRepo->updateOrInsert(type: 'bottom_banner', value: json_encode($bottomBannerImageArray));
        }

        $offerBanner = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'offer_banner']);
        if ($request->has('offer_banner')) {
            $offerBannerImage = !empty($offerBanner) ? $this->updateFile(dir: 'shop/', oldImage: (is_array($offerBanner['value']) ? $offerBanner['value']['image_name'] : $offerBanner['value'] ), format: 'webp', image: $request->file('offer_banner')) : $this->upload('shop/', 'webp',  $request->file('offer_banner'));
            $offerBannerImageArray = [
                'image_name' =>  $offerBannerImage,
                'storage' => config('filesystems.disks.default') ?? 'public'
            ];
            $this->businessSettingRepo->updateOrInsert(type: 'offer_banner', value: json_encode($offerBannerImageArray));
        }

        clearWebConfigCacheKeys();
        updateSetupGuideCacheKey(key: 'inhouse_shop_setup');
        ToastMagic::success(translate('Updated_successfully'));
        return back();
    }

    public function getTemporaryClose(Request $request): JsonResponse
    {
        $status = $request->get('status', 0);
        $this->businessSettingRepo->updateOrInsert(type: 'temporary_close', value: json_encode(['status' => $status]));
        Cache::forget(IN_HOUSE_SHOP_TEMPORARY_CLOSE_STATUS);
        updateSetupGuideCacheKey(key: 'inhouse_shop_setup');
        return response()->json(['status' => true, 'message' => translate('temporary_close_updated_successfully')], 200);
    }

    public function updateVacation(Request $request): RedirectResponse
    {
        $dates = explode(' - ', $request['vacation_date_duration']);
        $data = [
            'status' => $request['status'] == 'on' ? 1 : 0,
            'vacation_start_date' => isset($dates[0]) ? Carbon::createFromFormat('d M Y', $dates[0])->format('Y-m-d') : null,
            'vacation_end_date' => isset($dates[1]) ? Carbon::createFromFormat('d M Y', $dates[1])->format('Y-m-d') : null,
            'vacation_note' => $request['vacation_note']
        ];
        $this->businessSettingRepo->updateOrInsert(type: 'vacation_add', value: json_encode($data));
        clearWebConfigCacheKeys();
        ToastMagic::success(translate('vacation_mode_updated_successfully'));
        return redirect()->back();
    }

    public function getSetupView(Request $request): View
    {
        $minimumOrderAmountStatus = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'minimum_order_amount_status']);
        $minimumOrderAmount = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'minimum_order_amount']);
        $freeDeliveryStatus = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'free_delivery_status']);
        $freeDeliveryOverAmount = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'free_delivery_over_amount']);
        return view('admin-views.inhouse-shop.setup', compact('minimumOrderAmountStatus', 'minimumOrderAmount', 'freeDeliveryStatus', 'freeDeliveryOverAmount'));
    }

    public function updateSetup(Request $request): RedirectResponse
    {
        if ($request->has('minimum_order_amount')) {
            $this->businessSettingRepo->updateOrInsert(
                type: 'minimum_order_amount',
                value: currencyConverter(amount: $request->get('minimum_order_amount', 0))
            );
        }

        if ($request->has('free_delivery_over_amount')) {
            $this->businessSettingRepo->updateOrInsert(
                type: 'free_delivery_over_amount',
                value: currencyConverter(amount: $request->get('free_delivery_over_amount', 0))
            );
        }
        ToastMagic::success(translate('order_setup_updated_successfully'));
        return redirect()->back();
    }

}
