<div class="btn-group btn-group-sm date-preset-group mb-2" role="group">
    <button type="button" class="btn btn-outline-secondary" data-preset="today">Today</button>
    <button type="button" class="btn btn-outline-secondary" data-preset="this_week">This Week</button>
    <button type="button" class="btn btn-outline-secondary" data-preset="this_month">This Month</button>
    <button type="button" class="btn btn-outline-secondary" data-preset="last_month">Last Month</button>
    <button type="button" class="btn btn-outline-secondary" data-preset="this_year">This Year</button>
</div>

@push('js')
    <script>
        $(function () {
            $('.date-preset-group').on('click', 'button', function () {
                const group = $(this).closest('form');
                const fromInput = group.find('input[name="from_date"]');
                const toInput = group.find('input[name="to_date"]');

                const today = new Date();
                let from, to;

                function fmt(d) {
                    return d.toISOString().split('T')[0];
                }

                switch ($(this).data('preset')) {
                    case 'today':
                        from = to = today;
                        break;
                    case 'this_week': {
                        const day = today.getDay() === 0 ? 7 : today.getDay();
                        from = new Date(today);
                        from.setDate(today.getDate() - day + 1);
                        to = today;
                        break;
                    }
                    case 'this_month':
                        from = new Date(today.getFullYear(), today.getMonth(), 1);
                        to = today;
                        break;
                    case 'last_month':
                        from = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                        to = new Date(today.getFullYear(), today.getMonth(), 0);
                        break;
                    case 'this_year':
                        from = new Date(today.getFullYear(), 0, 1);
                        to = today;
                        break;
                }

                fromInput.val(fmt(from));
                toInput.val(fmt(to));
            });
        });
    </script>
@endpush
