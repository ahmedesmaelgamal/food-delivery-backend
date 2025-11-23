@extends('layouts.admin.app')

@section('title', translate('Priority_Setup'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 mb-sm-4">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                {{ translate('Priority_Setup') }}
            </h2>
        </div>

        <div class="bg-info bg-opacity-10 fs-12 px-12 py-10 text-dark rounded d-flex gap-2 align-items-center mb-20">
            <i class="fi fi-sr-lightbulb-on text-info"></i>
            <span>
                {{ translate('after_change_any_setup_in_this_page_must_click_the_save_information') }}
                <span class="fw-semibold">{{ translate('Save_Information') }}</span>
               {{ translate('button_otherwise_changes_are_not_work.') }}
            </span>
        </div>

        @include('admin-views.business-settings.priority-setup.partial.brand')
        @include('admin-views.business-settings.priority-setup.partial.category')
        @include('admin-views.business-settings.priority-setup.partial.vendor-list')
        @include('admin-views.business-settings.priority-setup.partial.featured-product')
        @if (theme_root_path() == 'default')
            @include('admin-views.business-settings.priority-setup.partial.new-arrival-product')
        @endif
        @include('admin-views.business-settings.priority-setup.partial.top-vendor')
        @include('admin-views.business-settings.priority-setup.partial.category-wise-product')
        @include('admin-views.business-settings.priority-setup.partial.top-rated-product')
        @include('admin-views.business-settings.priority-setup.partial.best-selling-product')
        @include('admin-views.business-settings.priority-setup.partial.searched-product')
        @include('admin-views.business-settings.priority-setup.partial.vendor-product-list')
    </div>

    @include("layouts.admin.partials.offcanvas._priority-setup")
@endsection
