@extends('layouts.admin.app')
@section('title', translate('general_Settings'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/new/back-end/css/component-snippets.css') }}">
    <link href="{{ dynamicAsset(path: 'public/assets/new/back-end/libs/highlight/highlight-default.min.css') }}" rel="stylesheet" />
@endpush

@section('content')

    <div class="container-fluid">
        <div class="d-flex flex-column gap-4">
            @include('layouts.admin.components.dropdown')
            @include('layouts.admin.components.inputs')
            @include('layouts.admin.components.buttons')
            @include('layouts.admin.components.icon-buttons')
            @include('layouts.admin.components.badge')
            @include('layouts.admin.components.tab-menu')
            @include('layouts.admin.components.toaster')
            @include('layouts.admin.components.tooltip')
            @include('layouts.admin.components.images')
            @include('layouts.admin.components.file-upload')
            @include('layouts.admin.components.modal')
            @include('layouts.admin.components.notes')
            @include('layouts.admin.components.table')
            @include('layouts.admin.components.offcanvas')
            @include('layouts.admin.partials._alert-message')
        </div>
    </div>

@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/new/back-end/libs/highlight/highlight.min.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            hljs.initHighlightingOnLoad();
            document.querySelectorAll(".component-snippets-code-container code").forEach((block) => {
                block.textContent = block.innerHTML.trim();
            });

            // Add copy functionality
            document.querySelectorAll('.copy-button').forEach(button => {
                button.addEventListener('click', () => {
                    const codeBlock = button.closest('.component-snippets-container').querySelector('.tab-pane.active code');
                    navigator.clipboard.writeText(codeBlock.textContent).then(() => {
                        // Change icon temporarily to show success
                        const icon = button.querySelector('i');
                        icon.classList.remove('fi-rr-copy');
                        icon.classList.add('fi-rr-check');

                        setTimeout(() => {
                            icon.classList.remove('fi-rr-check');
                            icon.classList.add('fi-rr-copy');
                        }, 1000);
                    });
                });
            });
        });
    </script>
@endpush

