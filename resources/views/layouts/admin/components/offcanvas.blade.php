<div class="card mt-3">
    <div class="card-body">
        <h3 class="text-primary">Offcanvas</h3>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2>
                   Our Commitments
                </h2>
                <p class="mb-0 fs-12">
                   This page you can setup you website Company Reliability section.
                </p>
           </div>
           <div>
               <button class="btn btn-outline-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFaqEdit">
                   <i class="fi fi-sr-eye"></i>
                   View OffCanvas
               </button>
           </div>
        </div>
    </div>
</div>

{{-- edit offcanvas --}}
<form action="" method="post" id="update-form-submit">
    @csrf
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasFaqEdit" aria-labelledby="offcanvasFaqEditLabel">
        <div class="offcanvas-header bg-body">
            <h2 class="mb-0">Edit FAQ</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mb-3 mb-sm-20">
                <div class="p-12 p-sm-20 bg-section rounded">
                    <div class="form-group">
                        <label class="form-label" for="">Question
                            <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Update Question" data-bs-title="Update Question">
                                <i class="fi fi-sr-info"></i>
                            </span>
                        </label>
                        <input type="text" class="form-control" name="question" id="question-filed"
                       placeholder="{{translate('type_Question')}}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="">Answer
                            <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Update Answer" data-bs-title="Update Answer">
                                <i class="fi fi-sr-info"></i>
                            </span>
                        </label>
                        <textarea class="form-control" name="answer"  id="answer-field"
                          rows="5" placeholder="{{translate('type_Answer')}}"></textarea>
                    </div>
                    <div class="form-group pb-3">
                        <label class="form-label" for="">Priority
                            <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Update Priority" data-bs-title="Update Priority">
                                <i class="fi fi-sr-info"></i>
                            </span>
                        </label>
                       <div class="select-wrapper">
                            <select class="form-select bg-white" name="ranking" id="ranking-field">
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
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