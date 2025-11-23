<form action="{{ route('admin.business-settings.inhouse-shop-vacation-update') }}" method="post">
    @csrf
    <div class="offcanvas offcanvas-end w-sm-500" tabindex="-1" id="offcanvasVacationMode" aria-labelledby="offcanvasVacationModeLabel">
        <div class="offcanvas-header bg-body">
            <h2 class="mb-0">{{ translate('Vacation_Mode_Setup') }}</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mb-3 mb-sm-20 d-flex flex-column gap-3 gap-sm-20">
                <div class="p-12 p-sm-20 bg-section rounded">
                    <div class="mb-3">
                        <h4>{{ translate('Vacation_Mode') }}</h4>
                        <p class="fs-12 mb-0">
                            {{ translate('if_you_turn_on_your_shop_will_go_to_vacation_mode.') }}
                            {{ translate('customer_can_view_your_shop_but_can_not_order_to_you') }}
                        </p>
                    </div>
                    <label class="d-flex justify-content-between align-items-center gap-3 border rounded px-3 py-2 user-select-none">
                        <span class="fw-medium text-dark">{{ translate('Status') }}</span>
                        <label class="switcher">
                            <input type="checkbox" name="status" class="switcher_input"
                                   id="vacation_close" {{ $vacation['status'] == 1 ? 'checked' : '' }}>
                            <span class="switcher_control"></span>
                        </label>
                    </label>
                </div>
                <div class="p-12 p-sm-20 bg-section rounded">
                    <div class="form-group">
                        <label class="form-label" for="custom_time_range">
                            {{ translate('Custom_Time_Range') }}
                            <span class="text-danger">*</span>
                        </label>
                        <?php
                        use Carbon\Carbon;
                        $vacationStartDate = isset($vacation['vacation_start_date'])
                            ? Carbon::parse($vacation['vacation_start_date'])
                            : now();
                        $vacationEndDate = isset($vacation['vacation_end_date'])
                            ? Carbon::parse($vacation['vacation_end_date'])
                            : now();
                        ?>
                        <div class="position-relative">
                            <span class="fi fi-sr-calendar icon-absolute-on-right"></span>
                            <input type="text" id="custom_time_range" class="js-daterangepicker form-control"
                                   name="vacation_date_duration" value="{{ $vacationStartDate?->format('d M Y') }} - {{ $vacationEndDate?->format('d M Y') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="vacation_note">
                            {{ translate('Vacation_Note') }}
                            <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top"
                                  aria-label="{{ translate('Vacation_Note') }}"
                                  data-bs-title="{{ translate('add_the_note_for_defining_the_reason_why_you_want_to_go_for_a_vacation_mode.') }}">
                                <i class="fi fi-sr-info"></i>
                            </span>
                        </label>
                        <textarea class="form-control" name="vacation_note" id="vacation_note" rows="5" placeholder="{{ translate('type_about_the_description') }}">{{ $vacation['vacation_note'] }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="offcanvas-footer shadow-lg">
            <div class="d-flex justify-content-center flex-wrap gap-3 bg-white px-3 py-2">
                <button type="reset" class="btn btn-secondary px-3 px-sm-4 flex-grow-1">{{ translate('reset') }}</button>
                <button type="submit" class="btn btn-primary px-3 px-sm-4 flex-grow-1">
                    {{ translate('update') }}
                </button>
            </div>
        </div>
    </div>
</form>
