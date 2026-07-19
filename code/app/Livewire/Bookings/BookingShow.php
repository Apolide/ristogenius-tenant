<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use App\Services\BookingService;
use App\Services\CustomerLanguageService;
use App\Services\MarketingForms\FormBlueprintService;
use App\Services\Messaging\BookingMessageService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BookingShow extends Component
{
    public Booking $booking;

    public bool $showPickTableModal = false;

    public array $tablePickerSelection = [];

    public string $searchTable = '';

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load('customer', 'tables.room', 'histories', 'formSubmissions.form');
    }

    public function showPickTable(): void
    {
        $this->tablePickerSelection = $this->booking->tables->pluck('id')->all();
        $this->showPickTableModal = true;
    }

    public function closePickTableModal(): void
    {
        $this->showPickTableModal = false;
        $this->searchTable = '';
    }

    public function saveSelectedTables(): void
    {
        $before = $this->booking->tables->pluck('name')->all();
        $this->booking->tables()->sync($this->tablePickerSelection);
        $this->booking->load('tables.room');
        $after = $this->booking->tables->pluck('name')->all();
        $this->booking->recordHistory('tables_changed', 'Tavoli assegnati modificati', ['tables' => ['from' => $before, 'to' => $after]]);
        $this->booking->load('histories');
        $this->closePickTableModal();
        session()->flash('success', __('bookings.messages.tables_saved'));
    }

    public function accept(BookingMessageService $messages): void
    {
        $this->changePendingStatus('accepted', $messages);
    }

    public function deny(BookingMessageService $messages): void
    {
        $this->changePendingStatus('denied', $messages);
    }

    public function render(BookingService $bookingService, CustomerLanguageService $languages, FormBlueprintService $formBlueprints)
    {
        $formSubmission = $this->booking->formSubmissions->sortByDesc('created_at')->first();
        $baseKeys = $formSubmission?->form
            ? collect($formBlueprints->fields($formSubmission->form->type))->pluck('key')
            : collect();
        $customFormFields = collect($formSubmission?->field_snapshot ?? [])
            ->reject(fn (array $field): bool => $baseKeys->contains($field['key'] ?? null))
            ->filter(fn (array $field): bool => array_key_exists($field['key'] ?? '', $formSubmission?->payload ?? []))
            ->map(function (array $field) use ($formSubmission): array {
                $language = $formSubmission->language ?: 'it';

                return [
                    'key' => $field['key'],
                    'label' => $field['label'][$language] ?? $field['label']['it'] ?? $field['key'],
                    'type' => $field['type'] ?? 'text',
                    'value' => $formSubmission->payload[$field['key']],
                ];
            })->values();

        return view('livewire.bookings.booking-show', [
            'tables' => $this->showPickTableModal
                ? $bookingService->availableTables($this->booking->booking_date->toDateString(), substr((string) $this->booking->booking_time, 0, 5), $this->booking->id, $this->searchTable)
                : collect(),
            'statusLabels' => config('bookings.statuses'),
            'bookingLanguage' => $languages->meta($this->booking->language ?: $this->booking->customer?->lang),
            'customFormFields' => $customFormFields,
            'sourceForm' => $formSubmission?->form,
        ])->title(__('bookings.detail'));
    }

    private function changePendingStatus(string $status, BookingMessageService $messages): void
    {
        abort_unless($this->booking->status === 'pending' && in_array($status, ['accepted', 'denied'], true), 422);
        DB::transaction(function () use ($status, $messages): void {
            $this->booking->update(['status' => $status]);
            $messages->bookingStatusChanged($this->booking->refresh());
        });
        $this->booking->load('histories');
        session()->flash('success', __('bookings.messages.saved'));
    }
}
