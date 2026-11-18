@extends('layouts.app')

@section('content')
<div class="content">
    <div class="main-content">
        <div class="mb-6 page-header-breadcrumb">
            <h1 class="page-title text-[1.3125rem] font-medium">{{ __('statistics.title') }}</h1>
        </div>

        <div class="box">
            <form method="get" action="{{ route('statistics.index') }}" class="box-body">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 md:col-span-3">
                        <label class="form-label" for="select-category">{{ __('statistics.category') }}</label>
                        <select class="form-select" id="select-category" name="category">
                            @foreach ($categories as $category => $metrics)
                                <option value="{{ $category }}" @selected($category === $filters['category'])>
                                    {{ __("statistics.categories.$category") }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-12 md:col-span-3">
                        <label class="form-label" for="select-subcategory">{{ __('statistics.metric') }}</label>
                        <select class="form-select" id="select-subcategory" name="metric">
                            @foreach ($categories[$filters['category']] as $metric)
                                <option value="{{ $metric }}" @selected($metric === $filters['metric'])>
                                    {{ __("statistics.metrics.$metric") }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 md:col-span-3">
                        <label class="form-label" for="statistics-from">{{ __('statistics.from') }}</label>
                        <input class="form-control" id="statistics-from" name="from" type="date" value="{{ $filters['from'] }}" required>
                    </div>
                    <div class="col-span-6 md:col-span-3">
                        <label class="form-label" for="statistics-to">{{ __('statistics.to') }}</label>
                        <input class="form-control" id="statistics-to" name="to" type="date" min="{{ $filters['from'] }}" value="{{ $filters['to'] }}" required>
                    </div>
                </div>
                <button class="ti-btn ti-btn-primary mt-4" type="submit">{{ __('statistics.apply') }}</button>
                @if ($filterError)
                    <p class="mt-3 text-danger" role="alert">{{ $filterError }}</p>
                @endif
            </form>
        </div>

        @if ($chart)
            <section class="box" aria-labelledby="statistics-chart-title">
                <div class="box-header">
                    <h2 class="box-title" id="statistics-chart-title">{{ __('statistics.metrics.'.$filters['metric']) }}</h2>
                </div>
                <div class="box-body">
                    @php
                        $description = __('statistics.descriptions.'.$filters['metric']);
                    @endphp
                    @if ($description !== 'statistics.descriptions.'.$filters['metric'])
                        <p class="text-textmuted mb-4">{{ $description }}</p>
                    @endif
                    @if ($chart['labels'])
                        <div style="height: {{ $chart['type'] === 'horizontal' ? max(360, min(900, count($chart['labels']) * 32)) : 360 }}px">
                            <canvas id="statistics-chart" role="img" aria-labelledby="statistics-chart-title"></canvas>
                        </div>
                        <details class="mt-4">
                            <summary>{{ __('statistics.data_table') }}</summary>
                            <div class="table-responsive">
                                <table class="ti-custom-table w-full">
                                    <thead><tr>
                                        <th scope="col">{{ __('statistics.group') }}</th>
                                        @foreach ($chart['datasets'] as $dataset)
                                            <th scope="col">{{ $dataset['label'] }}</th>
                                        @endforeach
                                    </tr></thead>
                                    <tbody>
                                        @foreach ($chart['labels'] as $index => $label)
                                            <tr>
                                                <th scope="row">{{ $label }}</th>
                                                @foreach ($chart['datasets'] as $dataset)
                                                    <td>{{ $dataset['data'][$index] === null ? '—' : \Illuminate\Support\Number::format($dataset['data'][$index], maxPrecision: 2, locale: app()->getLocale() === 'gr' ? 'el' : app()->getLocale()) }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </details>
                        <script src="{{ asset('assets/libs/chart.js/chart.min.js') }}"></script>
                    @else
                        <p class="text-textmuted">{{ __('statistics.empty') }}</p>
                    @endif
                </div>
            </section>
        @endif
    </div>
</div>

@php
    $metricOptions = collect($categories)->map(fn ($metrics) => collect($metrics)->mapWithKeys(
        fn ($metric) => [$metric => __("statistics.metrics.$metric")]
    ));
@endphp
<script>
(() => {
    const category = document.getElementById('select-category');
    const metric = document.getElementById('select-subcategory');
    const from = document.getElementById('statistics-from');
    const to = document.getElementById('statistics-to');
    const options = {{ Illuminate\Support\Js::from($metricOptions) }};

    category.addEventListener('change', () => {
        metric.replaceChildren(...Object.entries(options[category.value]).map(([value, label]) => new Option(label, value)));
    });

    from.addEventListener('input', () => {
        to.min = from.value;
    });

    const canvas = document.getElementById('statistics-chart');
    if (!canvas || !window.Chart) return;

    const chart = {{ Illuminate\Support\Js::from($chart) }};
    const locale = document.documentElement.lang;
    const colors = ['#635bdb', '#16856d', '#c06612', '#b44579', '#247abc', '#6a7b33', '#7951b0', '#a44d42'];
    const doughnut = chart.type === 'doughnut';
    const horizontal = chart.type === 'horizontal';
    const textColor = getComputedStyle(canvas.parentElement).color;
    const number = new Intl.NumberFormat(locale, { maximumFractionDigits: 2 });
    const datasets = chart.datasets.map((dataset, index) => ({
        ...dataset,
        backgroundColor: doughnut ? chart.labels.map((_, i) => colors[i % colors.length]) : colors[index],
        borderColor: doughnut ? 'transparent' : colors[index],
        borderWidth: doughnut ? 0 : 2,
        pointRadius: chart.labels.length > 60 ? 0 : 2,
    }));

    new Chart(canvas, {
        type: horizontal ? 'bar' : chart.type,
        data: { labels: chart.labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            locale,
            indexAxis: horizontal ? 'y' : 'x',
            interaction: { mode: doughnut ? 'nearest' : 'index', intersect: doughnut },
            plugins: {
                legend: { display: true, labels: { color: textColor } },
                tooltip: {
                    callbacks: {
                        label: (item) => {
                            const label = doughnut ? item.label : item.dataset.label;
                            const value = number.format(item.raw);
                            if (!doughnut) return label + ': ' + value;
                            const total = item.dataset.data.reduce((sum, count) => sum + count, 0);
                            const share = new Intl.NumberFormat(locale, { style: 'percent', maximumFractionDigits: 1 });
                            return label + ': ' + value + ' (' + share.format(total ? item.raw / total : 0) + ')';
                        },
                    },
                },
            },
            scales: doughnut ? {} : {
                x: { beginAtZero: horizontal, ticks: { color: textColor } },
                y: { beginAtZero: !horizontal, ticks: { color: textColor } },
            },
        },
    });
})();
</script>
@endsection
