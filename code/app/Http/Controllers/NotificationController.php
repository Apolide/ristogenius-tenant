<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $types = $request->user()->notifications()->getQuery()->select('notifications.type')
            ->distinct()->pluck('notifications.type')->all();
        $periods = ['all', 'today', 'week', 'month', 'custom'];
        $states = ['all', 'unread', 'read'];
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'type' => ['nullable', 'string', Rule::in($types)],
            'status' => ['nullable', Rule::in($states)],
            'period' => ['nullable', Rule::in($periods)],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('from'), 'after_or_equal:from')],
        ]);

        $period = $filters['period'] ?? 'all';
        $today = today();
        [$from, $until] = match ($period) {
            'today' => [$today, $today->copy()->addDay()],
            'week' => [$today->copy()->subDays(6), $today->copy()->addDay()],
            'month' => [$today->copy()->subDays(29), $today->copy()->addDay()],
            'custom' => [
                isset($filters['from']) ? Carbon::parse($filters['from'])->startOfDay() : null,
                isset($filters['to']) ? Carbon::parse($filters['to'])->startOfDay()->addDay() : null,
            ],
            default => [null, null],
        };

        $query = $request->user()->notifications()->latest('notifications.id');
        if (isset($filters['type'])) {
            $query->where('notifications.type', $filters['type']);
        }
        match ($filters['status'] ?? 'all') {
            'unread' => $query->wherePivotNull('read_at'),
            'read' => $query->wherePivotNotNull('read_at'),
            default => null,
        };
        if ($from) {
            $query->where('notifications.created_at', '>=', $from);
        }
        if ($until) {
            $query->where('notifications.created_at', '<', $until);
        }

        $search = trim($filters['q'] ?? '');
        $notifications = $search === '' ? $query->paginate(15) : $this->search($query, $search);

        $typeLabels = [];
        foreach ($types as $type) {
            $typeLabels[$type] = Notification::typeLabel($type);
        }
        asort($typeLabels, SORT_NATURAL | SORT_FLAG_CASE);

        return view('notifications.index', [
            'notifications' => $notifications->withQueryString(),
            'typeLabels' => $typeLabels,
            'periods' => $periods,
            'states' => $states,
            'filters' => $filters,
        ]);
    }

    private function search(BelongsToMany $query, string $search): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        $matches = [];
        $total = 0;

        // Messages with translation keys are rendered in PHP, so match the same text the user sees.
        foreach ($query->lazyByIdDesc(200) as $notification) {
            if (mb_stripos($notification->translatedTitle(), $search) === false
                && mb_stripos($notification->translatedMessage(), $search) === false) {
                continue;
            }

            if ($total >= $offset && count($matches) < $perPage) {
                $matches[] = $notification;
            }
            $total++;
        }

        return new LengthAwarePaginator($matches, $total, $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);
    }

    public function show(Request $request, int $notification): RedirectResponse
    {
        $user = $request->user();
        $entry = $user->notifications()->findOrFail($notification);
        $user->unreadNotifications()->updateExistingPivot($entry->id, ['read_at' => now()]);

        return filled($entry->url)
            ? redirect()->to($entry->url)
            : redirect()->route('notifications.index');
    }
}
