{{--
    Shared date/search filter bar for the return lists.

    Expects: $filters (q, from_date, to_date), $clearRoute, $searchLabel
--}}
<form method="get" class="filter-bar" data-no-submit-guard>
    <div class="row align-items-end">
        <div class="col-md-4">
            <div class="form-group">
                <label for="q">Search</label>
                <input type="text" name="q" id="q" class="form-control"
                    value="{{ $filters['q'] ?? '' }}" placeholder="{{ $searchLabel }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="from_date">From</label>
                <input type="date" name="from_date" id="from_date" class="form-control"
                    value="{{ $filters['from_date'] ?? '' }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="to_date">To</label>
                <input type="date" name="to_date" id="to_date" class="form-control"
                    value="{{ $filters['to_date'] ?? '' }}">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group page-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                @if (array_filter($filters))
                    <a href="{{ $clearRoute }}" class="btn btn-secondary">Clear</a>
                @endif
            </div>
        </div>
    </div>
</form>
