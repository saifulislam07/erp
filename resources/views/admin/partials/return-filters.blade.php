{{--
    Shared date/search filter bar for the return lists.

    Expects: $formId (the id ERP.serverTable binds to), $searchLabel
--}}
<form method="get" class="filter-bar" id="{{ $formId }}" data-no-submit-guard>
    <div class="row align-items-end">
        <div class="col-md-4">
            <div class="form-group">
                <label for="q">Search</label>
                <input type="text" name="q" id="q" class="form-control"
                    value="{{ request('q') }}" placeholder="{{ $searchLabel }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="from_date">From</label>
                <input type="date" name="from_date" id="from_date" class="form-control"
                    value="{{ request('from_date') }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="to_date">To</label>
                <input type="date" name="to_date" id="to_date" class="form-control"
                    value="{{ request('to_date') }}">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group page-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="#" class="btn btn-secondary" data-table-clear>Clear</a>
            </div>
        </div>
    </div>
</form>
