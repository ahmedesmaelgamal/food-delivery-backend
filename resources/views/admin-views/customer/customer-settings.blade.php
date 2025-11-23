@php use App\Utils\Convert; @endphp
@extends('layouts.admin.app')

@section('title', translate('customer_settings'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 mb-sm-20">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                {{ translate('business_Setup') }}
            </h2>
        </div>
        @include('admin-views.business-settings.business-setup-inline-menu')

        <form action="{{ route('admin.business-settings.customer-settings') }}" method="post" id="update-settings">
            @csrf

            <div class="d-flex flex-column gap-3">
                <div class="card">
                    <div class="card-body d-flex flex-column gap-3 gap-sm-20">
                        <div class="p-12 p-sm-20 bg-section rounded">
                            @php($walletStatus = getWebConfig(name: 'wallet_status'))
                            <div class="d-flex justify-content-between align-items-center gap-3">
                                <div>
                                    <h2>{{ translate('Customer_Wallet') }}</h2>
                                    <p class="mb-0">
                                        {{ translate('for_these_wallet_settings_customers_can_get_the_refund_to_the_wallet_and_also_can_use_their_wallet_money_to_pay_for_any_order') }}.
                                    </p>
                                </div>
                                <div>
                                    <label class="switcher" for="customer-wallet-status">
                                        <input
                                            class="switcher_input custom-modal-plugin"
                                            type="checkbox" value="1" name="customer_wallet"
                                            id="customer-wallet-status"
                                            {{ $walletStatus == 1 ? 'checked' : '' }}
                                            data-modal-type="input-change"
                                            data-on-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/customer-wallet-on.png') }}"
                                            data-off-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/customer-wallet-off.png') }}"
                                            data-on-title="{{ translate('want_to_Turn_ON_Customer_Wallet') }}"
                                            data-off-title="{{ translate('want_to_Turn_OFF_Customer_Wallet') }}"
                                            data-on-message="<p>{{ translate('if_enabled_customers_can_have_the_wallet_option_on_their_account_and_use_it_while_placing_orders_and_getting_refunds') }}</p>"
                                            data-off-message="<p>{{ translate('if_disabled_customer_wallet_option_will_be_hidden_from_their_account') }}</p>">
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="p-12 p-sm-20 bg-section rounded">
                            <div class="row g-4">
                                @php($addFundsToWallet = getWebConfig(name: 'add_funds_to_wallet'))
                                <div class="col-xl-4 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="">{{ translate('add_funds_to_wallet') }}
                                            <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="right"
                                                data-bs-title="{{ translate('enabling_the_option_customers_will_be_able_to_add_funds_to_the_wallet_through_the_available_payment_method') }}.">
                                                <i class="fi fi-sr-info"></i>
                                            </span>
                                        </label>
                                        <label
                                            class="d-flex justify-content-between align-items-center gap-3 border rounded px-3 py-10 bg-white user-select-none">
                                            <span class="fw-medium text-dark">{{ translate('status') }}</span>
                                            <label class="switcher" for="add-funds-to-wallet">
                                                <input
                                                    class="switcher_input custom-modal-plugin"
                                                    type="checkbox" value="1" name="add_funds_to_wallet"
                                                    id="add-funds-to-wallet"
                                                    {{ $walletStatus && $addFundsToWallet ? 'checked' : '' }}
                                                    {{ $walletStatus == 0 ? 'disabled' : '' }}
                                                    data-modal-type="input-change"
                                                    data-on-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/wallet-on.png') }}"
                                                    data-off-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/wallet-off.png') }}"
                                                    data-on-title="{{ translate('want_to_Turn_ON_Add_Fund_to_Wallet_option') }}"
                                                    data-off-title="{{ translate('want_to_Turn_OFF_Add_Fund_to_Wallet_option') }}"
                                                    data-on-message="<p>{{ translate('if_enabled_customers_can_add_money_to_their_wallet') }}</p>"
                                                    data-off-message="<p>{{ translate('if_disabled_customers_would_not_be_able_to_add_money_to_their_wallet') }}</p>">
                                                <span class="switcher_control"></span>
                                            </label>
                                        </label>
                                    </div>
                                </div>
                                @php($minimumAddFundAmount = getWebConfig(name: 'minimum_add_fund_amount'))
                                <div class="col-xl-4 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="">{{ translate('minimum_Add_Amount') }}
                                            ({{ getCurrencyCode(type: 'default') }})
                                        </label>
                                        <input type="text" class="form-control" name="minimum_add_fund_amount"
                                            id="minimum_add_fund_amount" placeholder="{{ translate('ex') . ': ' . '10' }}"
                                            value="{{ Convert::default($minimumAddFundAmount) ?? 0 }}"
                                            {{ $walletStatus == 0 ? 'disabled' : '' }}>
                                    </div>
                                </div>
                                @php($maximumAddFundAmount = getWebConfig(name: 'maximum_add_fund_amount'))
                                <div class="col-xl-4 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="">{{ translate('maximum_Add_Amount') }}
                                            ({{ getCurrencyCode(type: 'default') }})
                                        </label>
                                        <input type="text" class="form-control" name="maximum_add_fund_amount"
                                            id="maximum_add_fund_amount" placeholder="{{ translate('ex') . ': ' . '10' }}"
                                            value="{{ Convert::default($maximumAddFundAmount) ?? 0 }}"
                                            {{ $walletStatus == 0 ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body d-flex flex-column gap-3 gap-sm-20">
                        @php($loyaltyPointStatus = getWebConfig(name: 'loyalty_point_status'))
                        <div class="p-12 p-sm-20 bg-section rounded">
                            <div class="d-flex justify-content-between align-items-center gap-3">
                                <div>
                                    <h2>{{ translate('Customer_Loyalty_Point') }}</h2>
                                    <p class="mb-0">
                                        {{ translate('in_this_settings_admin_can_set_the_rules_for_the_customers_for_earning_and_use_the_loyalty_points') }}.
                                    </p>
                                </div>
                                <div>
                                    <label class="switcher" for="customer-loyalty-point">
                                        <input
                                            class="switcher_input custom-modal-plugin"
                                            type="checkbox" value="1" name="customer_loyalty_point"
                                            id="customer-loyalty-point"
                                            {{ $loyaltyPointStatus ? 'checked' : '' }}
                                            data-modal-type="input-change"
                                            data-on-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/loyalty-on.png') }}"
                                            data-off-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/loyalty-off.png') }}"
                                            data-on-title="{{ translate('want_to_Turn_ON_Loyalty_Point') }}"
                                            data-off-title="{{ translate('want_to_Turn_OFF_Loyalty_Point') }}"
                                            data-on-message="<p>{{ translate('if_enabled_the_loyalty_point_option_will_be_available_to_the_customers_account') }}</p>"
                                            data-off-message="<p>{{ translate('if_disabled_loyalty_point_option_will_be_hidden_from_the_customers_account') }}</p>">
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="p-12 p-sm-20 bg-section rounded">
                            <div class="row g-4">
                                @php($loyaltyPointExchangeRate = getWebConfig(name: 'loyalty_point_exchange_rate'))
                                <div class="col-xl-4 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="loyalty_point_exchange_rate">
                                            {{ translate('Equivalent_Points_Needed_to_Redeem') }} {{ setCurrencySymbol(amount: 1) }}
                                        </label>
                                        <input type="text" class="form-control" name="loyalty_point_exchange_rate"
                                            {{ $loyaltyPointStatus == 0 ? '' : 'required' }}
                                            id="loyalty_point_exchange_rate"
                                            placeholder="{{ translate('ex') . ': ' . '10' }}"
                                            value="{{ $loyaltyPointExchangeRate ?? 0 }}" required>
                                    </div>
                                </div>
                                @php($loyaltyPointMinimumPoint = getWebConfig(name: 'loyalty_point_minimum_point'))
                                <div class="col-xl-4 col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="minimum_transfer_point">
                                            {{ translate('minimum_Point_Required_To_Convert') }}
                                            <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="right"
                                                data-bs-title="{{ translate('this_point_is_the_required_amount_which_is_needed_to_convert_the_point_to_the_wallet_balance') }}">
                                                <i class="fi fi-sr-info"></i>
                                            </span>
                                        </label>
                                        <input type="text" class="form-control" name="minimum_transfer_point"
                                            id="minimum_transfer_point" placeholder="{{ translate('ex') . ': ' . '2' }}"
                                            {{ $loyaltyPointStatus == 0 ? '' : 'required' }}
                                            value="{{ $loyaltyPointMinimumPoint ?? 0 }}" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-12 p-sm-20 bg-section rounded">
                            <div class="row g-4 align-items-center">
                                <div class="col">
                                    <div>
                                        <h4 class="fw-medium">{{ translate('Earn_Loyalty_Point_on_Each_Order') }}</h4>
                                        <p class="mb-0">
                                            {{ translate('setup_loyalty_point_percentage_earned_by_customer_based_on_order_amount') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-12">
                                    @php($loyaltyPointItemPurchasePoint=getWebConfig(name:'loyalty_point_item_purchase_point'))
                                    <div class="form-group">
                                        <label class="form-label" for="">
                                            {{ translate('Earning_Percentage') }} (%)
                                        </label>
                                        <input type="text" class="form-control" name="item_purchase_point" id=""
                                            placeholder="{{ translate('ex') . ': ' . '2' }}" value="{{ $loyaltyPointItemPurchasePoint ?? 1 }}"  {{ $loyaltyPointStatus == 1 ? 'required' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body d-flex flex-column gap-3 gap-sm-20">
                        @php($refEarningStatus = getWebConfig(name: 'ref_earning_status'))
                        <div class="p-12 p-sm-20 bg-section rounded">
                            <div class="d-flex justify-content-between align-items-center gap-3">
                                <div>
                                    <h2>{{ translate('customer_referral_earning_settings') }}</h2>
                                    <p class="mb-0">
                                        {{ translate('Customers_will_receive_this_wallet_balance_rewards_for_sharing_their_referral_code') }}
                                    </p>
                                </div>
                                <div>
                                    <label class="switcher" for="ref-earning-status">
                                        <input
                                            class="switcher_input custom-modal-plugin"
                                            type="checkbox" value="1" name="ref_earning_status"
                                            id="ref-earning-status"
                                            {{ $refEarningStatus ? 'checked' : '' }}
                                            data-modal-type="input-change"
                                            data-on-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/referral-earning-on.png') }}"
                                            data-off-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/referral-earning-off.png') }}"
                                            data-on-title="{{ translate('want_to_Turn_ON_Referral_And_Earning_option') }}"
                                            data-off-title="{{ translate('want_to_Turn_OFF_Referral_And_Earning_option') }}"
                                            data-on-message="<p>{{ translate('if_enabled_customers_will_receive_rewards_for_each_successful_referral') }}</p>"
                                            data-off-message="<p>{{ translate('if_disabled_customers_will_not_receive_rewards_for_successful_referral') }}</p>">
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="p-12 p-sm-20 bg-section rounded">
                            <div class="row g-4 align-items-center">
                                @php($refEarningExchangeRate = getWebConfig(name: 'ref_earning_exchange_rate'))
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label" for="ref_earning_exchange_rate">
                                            {{ translate('earnings_to_Each_Referral') }}
                                            ({{ getCurrencyCode(type: 'default') }})
                                        </label>
                                        <input type="text" class="form-control" name="ref_earning_exchange_rate"
                                            id="ref_earning_exchange_rate"
                                            placeholder="{{ translate('ex') . ': ' . '10' }}"
                                            {{ $refEarningStatus == 0 ? '' : 'required' }}
                                            value="{{ Convert::default($refEarningExchangeRate) ?? 0 }}">
                                        <p class="text-danger mt-1 mb-0">
                                            {{ translate('must_turn_on_add_fund_to_wallet_option_otherwise_customer_can_not_receive_the_reward_amount') }}.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end trans3 mt-4">
                <div class="d-flex justify-content-sm-end justify-content-center gap-3 flex-grow-1 flex-grow-sm-0 bg-white action-btn-wrapper trans3">
                    <button type="reset" class="btn btn-secondary px-3 px-sm-4 w-120">{{ translate('reset') }}</button>
                    <button type="submit" class="btn btn-primary px-3 px-sm-4">
                        <i class="fi fi-sr-disk"></i>
                        {{ translate('save_information') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
    @include("layouts.admin.partials.offcanvas._customer-settings")
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/new/back-end/js/business-setting.js') }}"></script>
@endpush
