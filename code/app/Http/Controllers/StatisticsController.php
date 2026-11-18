<?php

namespace App\Http\Controllers;

use App\Services\StatisticsService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function __invoke(Request $request, StatisticsService $statistics): View
    {
        $categories = StatisticsService::CATEGORIES;
        $today = CarbonImmutable::today();
        $defaults = [
            'category' => 'bookings',
            'from' => $today->subDays(29)->toDateString(),
            'to' => $today->toDateString(),
        ];
        $filters = array_replace($defaults, $request->only('category', 'metric', 'from', 'to'));
        $category = $filters['category'];
        $metrics = is_string($category) ? ($categories[$category] ?? []) : [];
        $filters['metric'] ??= $metrics[0] ?? '';
        $validator = Validator::make($filters, [
            'category' => ['bail', 'required', 'string', Rule::in(array_keys($categories))],
            'metric' => ['bail', 'required', 'string', Rule::in($metrics)],
            'from' => ['bail', 'required', 'date_format:Y-m-d'],
            'to' => ['bail', 'required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $validator->after(function ($validator) use ($filters): void {
            if ($validator->errors()->hasAny(['from', 'to'])) {
                return;
            }

            if (CarbonImmutable::parse($filters['from'])->addDays(365)->lt($filters['to'])) {
                $validator->errors()->add('to', __('statistics.range_error'));
            }
        });

        if ($validator->fails()) {
            $fallbackCategory = $metrics ? $category : $defaults['category'];
            $filters = [
                ...$defaults,
                'category' => $fallbackCategory,
                'metric' => in_array($filters['metric'], $categories[$fallbackCategory], true)
                    ? $filters['metric'] : $categories[$fallbackCategory][0],
                'from' => $validator->errors()->has('from') ? $defaults['from'] : $filters['from'],
                'to' => is_string($filters['to']) ? $filters['to'] : $defaults['to'],
            ];

            return view('statistics.index', [
                'categories' => $categories,
                'filters' => $filters,
                'chart' => null,
                'filterError' => $validator->errors()->hasAny(['from', 'to'])
                    ? __('statistics.range_error') : __('statistics.filter_error'),
            ]);
        }

        return view('statistics.index', [
            'categories' => $categories,
            'filters' => $filters,
            'chart' => $statistics->chart($filters['metric'], $filters['from'], $filters['to']),
            'filterError' => null,
        ]);
    }
}
