@extends('layouts.admin.app')

@section('title', translate('inhouse_shop'))

@section('content')
    <div class="content container-fluid">

        <div class="row g-3 align-items-center mb-3">
            <div class="col-md-12">
                <h1 class="mb-3 sm-sm-20">
                    {{ translate('In-house_Shop') }}
                </h1>
                @include("admin-views.inhouse-shop._inhouse-shop-menu")
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.business-settings.inhouse-shop-temporary-close') }}" method="post"
                      id="shop-availability-status-form" class="no-reload-form">
                    @csrf

                    <div class="row align-items-center">
                        <div class="col-md-8 col-xl-9">
                            <div>
                                <h3 class="fs-18">{{ translate('Temporary_Close') }}</h3>
                                <p class="mb-0 fs-12">
                                    {{ translate('Turning_on_the_status_will_deactivate_your_shop_and_will_show_temporary_off_in_the_customer_app_and_websites') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-xl-3">
                            <div class="mt-3 mt-md-0">
                                <label
                                    class="d-flex justify-content-between align-items-center gap-3 border rounded px-20 py-3 user-select-none">
                                    <span class="fw-medium text-dark">{{ translate('Status') }}</span>
                                    <label class="switcher" for="shop-availability-status">
                                        <input
                                            class="switcher_input custom-modal-plugin"
                                            type="checkbox" value="1" name="status"
                                            id="shop-availability-status"
                                            {{ $temporaryClose['status'] == 1 ? 'checked' : '' }}
                                            data-modal-type="input-change-form"
                                            data-modal-form="#shop-availability-status-form"
                                            data-on-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/store-temporary-close-on.png') }}"
                                            data-off-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/store-temporary-close-off.png') }}"
                                            data-on-title="{{ translate('want_to_turn_on_the_Temporary_Close_status') }}"
                                            data-off-title="{{ translate('want_to_turn_off_the_Temporary_Close_status') }}"
                                            data-on-message="<p>{{ translate('if_you_turn_on_the_option,_your_store_will_show_temporarily_close_in_the_customer_app_and_the_website') }}</p>"
                                            data-off-message="<p>{{ translate('if_you_turn_off_the_option,_your_store_will_hide_temporarily_close_in_the_customer_app_and_the_website') }}</p>"
                                            data-on-button-text="{{ translate('turn_on') }}"
                                            data-off-button-text="{{ translate('turn_off') }}">
                                        <span class="switcher_control"></span>
                                    </label>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-wrap gap-3 justify-content-between">
                    <div class="d-flex flex-column gap-1">
                        <h2 class="mb-1">{{ translate('shop_details') }}</h2>
                        <p class="fs-12 mb-0">{{ translate('created_at') }} {{ date('d M, Y', strtotime($admin['updated_at']) ) }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-3">
                        <button class="btn btn-secondary lh-1" data-bs-toggle="offcanvas"
                                data-bs-target="#offcanvasVacationMode">
                            <i class="fi fi-sr-umbrella-beach"></i>
                            {{ translate('Go_to_Vacation_Mode') }}
                        </button>
                        <a href="{{ route('admin.business-settings.inhouse-shop').'?action=edit' }}"
                           class="btn btn-primary lh-1">
                            <i class="fi fi-sr-pen-circle"></i>
                            {{ translate('Edit_Information') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body d-flex flex-column gap-sm-20 gap-3">

                @if($vacation['status'] != 1 && $temporaryClose['status'] != 1)
                    <div
                        class="bg-warning bg-opacity-10 fs-12 px-12 py-10 text-dark rounded d-flex gap-2 align-items-center">
                        <i class="fi fi-sr-info text-warning"></i>
                        <span>
                            {{ translate('Your_shop_is_now_on_live_status.') }}
                            {{ translate('All_functions_are_work_properly.') }}
                            {{ translate('To_turn_vacation_mode_click_go_vacation_mode_button.') }}
                        </span>
                    </div>
                @endif

                @if($temporaryClose['status'] == 1)
                    <div
                        class="bg-warning bg-opacity-10 fs-12 px-12 py-10 text-dark rounded d-flex gap-2 align-items-center">
                        <i class="fi fi-sr-info text-warning"></i>
                        <span>
                            {{ translate('your_shop_is_not_live_now.') }}
                            {{ translate('shop_availability_status_switch_is_turned_off.') }}
                        </span>
                    </div>
                @endif

                @if($vacation['status'] == 1 && date('Y-m-d') >= $vacation['vacation_start_date'] && date('Y-m-d') <= $vacation['vacation_end_date'])
                    <div
                        class="bg-danger bg-opacity-10 fs-12 px-12 py-10 text-dark rounded d-flex gap-2 align-items-center">
                        <i class="fi fi-sr-info text-danger"></i>
                        <span>
                            {{ translate('your_shop_is_now_vacation_mode_from') }} <span
                                class="text-danger fw-semibold">{{ Carbon\Carbon::parse($vacation['vacation_start_date'])?->format('d M Y') }} - {{ Carbon\Carbon::parse($vacation['vacation_end_date'])?->format('d M Y') }}.
                            </span> {{ translate('to_turn_off_vacation_mode_click')}} <strong> {{ translate('Go_Vacation_Mode') }}</strong>
                            {{ translate('_button_and_turn_the_status_switch_off.') }}) }}
                        </span>
                    </div>
                @endif

                <div
                    class="admin-inhouse-banner rounded-10 min-h-260 d-flex align-items-end after_upload_banner_overlay"
                    style="background-image: url('{{ getStorageImages(path: getWebConfig(name: 'shop_banner'), type:'backend-banner', source: dynamicAsset('public/assets/new/back-end/img/inhouse-banner.png')) }}')"
                >
                    <div class="p-20 bg-white bg-opacity-90 w-100 z-1 d-flex justify-content-between gap-3 flex-wrap">
                        <div class="media flex-wrap gap-3 align-items-baseline mt-n50">
                            <div class="bg-white rounded p-20 p-sm-30 shadow-lg">
                                <img height="50" class="ratio-1"
                                     src="{{ getStorageImages(path: getWebConfig(name: 'company_fav_icon'), type: 'backend-logo') }}"
                                     alt="">
                            </div>
                            <div class="media-body">
                                <h2 class="fw-bold">{{ getWebConfig(name: 'company_name' ) }} {{ translate('shop') }}</h2>
                                <p class="fs-12 mb-0">
                                    {{ translate('created_at') }} {{ date('d M, Y', strtotime($admin['updated_at']) ) }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('shopView',['id' => 0]) }}" target="_blank"
                               class="btn btn-outline-primary lh-1 text-nowrap">
                                {{ translate('visit_website') }}
                                <i class="fi fi-rr-up-right-from-square"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include("admin-views.inhouse-shop._vacation-mode-offcanvas")

    </div>

    @include("layouts.admin.partials.offcanvas._inhouse-shop-setup")
@endsection
