<input id="{{ $fieldId }}" type="date" wire:model.live="answers.date"
       class="form-control cursor-pointer dark:[color-scheme:dark]"
       onclick="if (this.showPicker) this.showPicker()"
       min="{{ $minimumDate }}" @if($maximumDate) max="{{ $maximumDate }}" @endif
       @required($field->required)>
@if ($eventMode === 'dates')
    <p class="mt-1 text-xs text-textmuted">Date disponibili: {{ implode(', ', $eventSchedule['dates'] ?? []) }}</p>
@endif
