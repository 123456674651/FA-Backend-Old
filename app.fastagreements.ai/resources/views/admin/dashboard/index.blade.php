@extends('admin.layout.admin')
@section('content')
    <!-- End Sidebar-->
    <main id="main" class="main">

        @php
            $rangeFrom = request('from_date') ? \Carbon\Carbon::parse(request('from_date')) : now()->startOfMonth();
            $rangeTo = request('to_date') ? \Carbon\Carbon::parse(request('to_date')) : now()->endOfMonth();
        @endphp

        <div class="dash-greeting">
            <div>
                @php
                    $hour = now()->hour;
                    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
                @endphp
                <h1>{{ $greeting }}, {{ explode(' ', auth()->user()->name)[0] }}!</h1>
                <p>Here's what's happening with your dashboard today.</p>
            </div>

            <form method="GET" action="{{ route('dashboard.index') }}" id="dashDateForm" class="dash-date-form">
                <input type="hidden" name="from_date" id="from_date_input" value="{{ $rangeFrom->format('Y-m-d') }}">
                <input type="hidden" name="to_date" id="to_date_input" value="{{ $rangeTo->format('Y-m-d') }}">
                <button type="button" class="dash-date-range" id="dashDateRangeBtn">
                    <span id="dashDateRangeLabel">{{ $rangeFrom->format('d M, Y') }} to {{ $rangeTo->format('d M, Y') }}</span>
                    <i class="bi bi-calendar3"></i>
                </button>
            </form>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">

                <!-- Statistics Cards -->
                <div class="col-12 mb-4">
                    <div class="row g-3">
                        @php
                            $stats = [
                                ['key' => 'totalRegisteredUsers', 'label' => 'Total Registered Users', 'value' => $totalRegisteredUsers ?? 0, 'icon' => 'bi-people-fill', 'color' => 'blue'],
                                ['key' => 'totalAgreements', 'label' => 'Total Agreements', 'value' => $totalAgreements ?? $dealsCount ?? 0, 'icon' => 'bi-file-earmark-text', 'color' => 'green'],
                                ['key' => 'todaysAgreements', 'label' => "Today's Agreements", 'value' => $todaysAgreements ?? 0, 'icon' => 'bi-calendar-day', 'color' => 'amber'],
                                ['key' => 'monthlyAgreements', 'label' => 'Monthly Agreements', 'value' => $monthlyAgreements ?? 0, 'icon' => 'bi-calendar4-week', 'color' => 'cyan'],
                                ['key' => 'activeCategories', 'label' => 'Active Categories', 'value' => $activeCategories ?? 0, 'icon' => 'bi-tags', 'color' => 'slate'],
                                ['key' => 'activeAdvocates', 'label' => 'Active Advocates', 'value' => $activeAdvocates ?? 0, 'icon' => 'bi-person-badge', 'color' => 'indigo'],
                                ['key' => 'activeCustomers', 'label' => 'Active Customers', 'value' => $activeCustomers ?? $customersCount ?? 0, 'icon' => 'bi-people', 'color' => 'teal'],
                            ];
                        @endphp

                        @foreach ($stats as $stat)
                            <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                                <div class="stat-card">
                                    <div class="stat-card-top">
                                        <span class="stat-label">{{ $stat['label'] }}</span>
                                        <span class="stat-icon stat-icon-{{ $stat['color'] }}">
                                            <i class="bi {{ $stat['icon'] }}"></i>
                                        </span>
                                    </div>
                                    <div class="stat-value" data-count data-stat="{{ $stat['key'] }}">{{ $stat['value'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>


            

            </div>
        </section>

    </main>

    <!-- End #main -->
    <script>
        // Simple animated counter for elements with [data-count]
        document.addEventListener('DOMContentLoaded', function() {
            function animate(el, target) {
                var start = 0;
                var duration = 900;
                var startTime = null;

                function step(timestamp) {
                    if (!startTime) startTime = timestamp;
                    var progress = Math.min((timestamp - startTime) / duration, 1);
                    var current = Math.floor(progress * (target - start) + start);
                    el.textContent = current;
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    } else {
                        el.textContent = target;
                    }
                }
                window.requestAnimationFrame(step);
            }

            document.querySelectorAll('[data-count]').forEach(function(h) {
                var val = parseInt(h.textContent.replace(/,/g, '')) || 0;
                h.textContent = '0';
                if (val > 0) animate(h, val);
            });
        });
    </script>

@stop

@section('js')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>
    <script>
        $(function () {
            var $btn = $('#dashDateRangeBtn');
            var $label = $('#dashDateRangeLabel');
            var $from = $('#from_date_input');
            var $to = $('#to_date_input');

            $btn.daterangepicker({
                startDate: moment($from.val()),
                endDate: moment($to.val()),
                opens: 'left',
                autoApply: false,
                locale: { format: 'DD MMM, YYYY' }
            }, function (start, end) {
                $from.val(start.format('YYYY-MM-DD'));
                $to.val(end.format('YYYY-MM-DD'));
                $label.text(start.format('DD MMM, YYYY') + ' to ' + end.format('DD MMM, YYYY'));

                var $cards = $('.stat-card').addClass('stat-card-loading');

                $.get('{{ route('dashboard.index') }}', {
                    from_date: $from.val(),
                    to_date: $to.val()
                }, function (data) {
                    $('[data-stat]').each(function () {
                        var val = data[$(this).data('stat')] ?? 0;
                        $(this).text(val);
                    });
                    if (window.history.replaceState) {
                        window.history.replaceState(null, '', '{{ route('dashboard.index') }}?from_date=' + $from.val() + '&to_date=' + $to.val());
                    }
                }).always(function () {
                    $cards.removeClass('stat-card-loading');
                });
            });
        });
    </script>
@endsection
