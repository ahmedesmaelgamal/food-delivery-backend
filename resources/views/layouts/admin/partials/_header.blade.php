@php
    $last5Orders = \App\Models\Order::where('order_status', 'pending')->with('details.productAllStatus')->orderBy('id', 'desc')->take(5)->get();
@endphp

<header class="header fixed-top navbar-fixed shadow-sm bg-white">
    <div class="d-flex align-items-center justify-content-between gap-3">
        <div class="">
            <button type="button" class="d-none d-lg-block btn-icon border-0">
                <i class="fi fi-rr-menu-burger" data-bs-toggle="tooltip" data-bs-title="Expand"></i>
            </button>
            <button type="button" class="d-lg-none p-0 bg-transparent border-0" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasAside">
                <i class="fi fi-rr-menu-burger"></i>
            </button>
        </div>

        <div class="navbar-nav-wrap-content-right">
            <ul class="navbar-nav align-items-center flex-row gap-3">
                <li class="nav-item">
                    <a class="btn-icon" href="{{ route('home') }}" target="_blank" data-bs-toggle="tooltip"
                       data-bs-title="{{ translate('Website') }}">
                        <i class="fi fi-rr-globe fs-18"></i>
                    </a>
                </li>

                <li class="nav-item">
                    @php( $local = session()->has('local') ? session('local'):'en')
                    @php($lang = \App\Models\BusinessSetting::where('type', 'language')->first())
                    <div class="topbar-text dropdown">
                        <a class="btn-icon topbar-link" href="javascript:" data-bs-toggle="dropdown">
                            @foreach(json_decode($lang['value'],true) as $data)
                                @if($data['code']==$local)
                                    <img width="20"
                                         src="{{ dynamicAsset(path: 'public/assets/front-end/img/flags/'.$data['code'].'.png') }}"
                                         alt="{{ $data['name']}}">
                                @endif
                            @endforeach
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @foreach(json_decode($lang['value'],true) as $key =>$data)
                                @if($data['status']==1)
                                    <li class="change-language" data-action="{{ route('change-language') }}"
                                        data-language-code="{{ $data['code']}}">
                                        <a class="d-flex gap-2 align-items-center justify-content-between dropdown-item {{ $data['code']==$local ? 'active' : ':'}}"
                                           href="javascript:">
                                            <div class="d-flex gap-2 align-items-center">
                                                <img width="20"
                                                     src="{{ dynamicAsset(path: 'public/assets/front-end/img/flags/'.$data['code'].'.png') }}"
                                                     alt="{{ $data['name']}}"/>
                                                <span class="text-capitalize">{{ $data['name']}}</span>
                                            </div>
                                            {!! $data['code'] == $local ? '<i class="fi fi-rr-check"></i>' : '' !!}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </li>

                @if(\App\Utils\Helpers::module_permission_check('order_management'))
                    <li class="dropdown nav-item">
                        <a class="btn-icon" href="{{ route('admin.orders.list',['status'=>'pending']) }}"
                           data-bs-toggle="dropdown">
                            @php($pendingOrderCount = \App\Models\Order::where('order_status','pending')->count())
                            <div class="position-relative">
                                <i class="fi fi-sr-shopping-cart fs-18"></i>
                                @if($pendingOrderCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $pendingOrderCount > 99 ? '99+' : $pendingOrderCount }}
                                    <span class="visually-hidden">{{ translate('pending_Orders') }}</span>
                                </span>
                                @endif
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-cart dropdown-menu-end">
                            <div class="d-flex flex-column gap-2 px-3 pb-2">
                                <button type="button" class="d-flex d-sm-none btn-close border-0 btn-circle w-20 h-20 p-1 fs-10 bg-section2 shadow-none position-absolute top-0 inset-inline-end-0 m-2" data-bs-dismiss="modal" aria-label="Close"></button>
                                <div class="d-flex flex-wrap flex-column flex-sm-row column-gap-1 row-gap-3 justify-content-between py-2">
                                    <div class="d-flex gap-2 align-items-center">
                                        <h4 class="text-capitalize mb-0">{{ translate('total_orders') }}</h4>
                                        <span class="text-body-light">
                                            ({{\App\Models\Order::where('order_status','pending')->count() }})
                                        </span>
                                    </div>
                                    <a href="{{ route('admin.orders.list',['status'=>'pending']) }}"
                                       class="text-primary d-flex gap-1 lh-1">{{ translate('view_all') }} <i class="fi fi-rr-arrow-small-right"></i></a>
                                </div>
                                <div class="overflow-auto max-h-65vh bg-body rounded">
                                    <div class="py-2 bg-body rounded">
                                        <table
                                        class="table bg-transparent table-borderless align-middle">
                                            <tbody>
                                                @foreach($last5Orders as $order)
                                                    <tr>
                                                        <td>
                                                            <div>
                                                                <div class="d-flex align-items-center flex-shrink-0 w-100px">
                                                                    @php($productImages = [])
                                                                    @foreach($order->details as $details)
                                                                        @if($details?->productAllStatus?->thumbnail_full_url && $details?->productAllStatus?->thumbnail_full_url['status'] === 200)
                                                                        @php($productImages[] = $details?->productAllStatus?->thumbnail_full_url)
                                                                        @endif
                                                                    @endforeach
                                                                    @if(count($productImages))
                                                                        @foreach($productImages as $imageKey => $productImage)
                                                                            <div class="w-100 ms-n-6px dropdown-cart-image {{ $imageKey == 2 ? 'position-relative' : '' }} {{ $imageKey > 2 ? 'd-none' : '' }}">
                                                                                <img
                                                                                    class="border bg-white rounded object-fit-cover w-100 shadow-left"
                                                                                    height="36" alt="product image"
                                                                                    src="{{ getStorageImages(path: $productImage, type: 'backend-product') }}">
                                                                                @if($imageKey == 2 && count($productImages) > 3)
                                                                                    <div class="extra-images rounded">
                                                                                        <span class="extra-image-count">
                                                                                            + {{ count($productImages) - 3 }}
                                                                                        </span>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        @endforeach
                                                                    @else
                                                                        <img class="border bg-white rounded object-fit-cover w-100"
                                                                            height="36" src="https://placehold.co/40x40" alt="placeholder">
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex flex-column gap-1 fs-12">
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <div class="min-w-80 text-start">{{ translate('Order_id') }}</div>
                                                                    :
                                                                    <span class="text-dark">{{ $order->id }}</span>
                                                                </div>
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <div class="min-w-80 text-start">{{ translate('Order_Amount') }}</div>
                                                                    :
                                                                    <span class="text-dark">
                                                                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $order->order_amount), currencyCode: getCurrencyCode()) }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex justify-content-end">
                                                                <a href="{{ route('admin.orders.details', ['id' => $order['id']]) }}"
                                                                class="btn btn-outline-primary btn-square">
                                                                    <i class="fi fi-sr-eye"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                     </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endif

                @if(\App\Utils\Helpers::module_permission_check('support_section'))
                    <li class="nav-item">
                        <a class="btn-icon" href="{{ route('admin.contact.list') }}"
                           data-bs-title="{{ translate('message') }}" data-bs-toggle="tooltip">
                            <i class="fi fi-sr-comment-alt-dots fs-18"></i>

                            @php($message=\App\Models\Contact::where('seen',0)->count())
                            @if($message!=0)
                                <span class="btn-status btn-sm-status btn-status-danger">
                                    {{ $message }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endif

                <li class="nav-item">
                    <div class="dropdown">
                        <a class="d-flex" href="javascript:" data-bs-toggle="dropdown">
                            <img class="rounded-circle border border-2 min-w-36 aspect-1" width="36"
                                 src="{{ getStorageImages(path: auth('admin')->user()->image_full_url, type: 'backend-profile') }}"
                                 alt="{{ translate('image_description') }}">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <div class="dropdown-item">
                                <div class="media gap-2 align-items-center">
                                    <img class="rounded-circle border border-2 aspect-1" width="40"
                                         src="{{ getStorageImages(path: auth('admin')->user()->image_full_url, type: 'backend-profile') }}"
                                         alt="{{ translate('image_description') }}">

                                    <div class="media-body">
                                        <h4 class="fw-bold mb-1">{{ auth('admin')->user()->name }}</h4>
                                        <p class="fs-12 text-body-light fw-medium">
                                            {{ ucwords(auth('admin')->user()->role->name) ?? '' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item media gap-2 align-items-center"
                               href="{{ route('admin.profile.update', ['id' => auth('admin')->user()->id]) }}">
                                <i class="fi fi-rr-settings text-body-light"></i>
                                <span class="text-truncate media-body" title="{{ translate('settings') }}">
                                    {{ translate('settings') }}
                                </span>
                            </a>
                            <a class="dropdown-item media gap-2 align-items-center" href="javascript:"
                               data-bs-toggle="modal" data-bs-target="#sign-out-modal">
                                <i class="fi fi-sr-sign-out-alt text-body-light"></i>
                                <span class="text-truncate media-body" title="{{ translate('logout') }}">
                                    {{ translate('logout') }}
                                </span>
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>
