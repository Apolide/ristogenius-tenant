@once
    @push('styles')
        <link rel="stylesheet" href="/assets/libs/flatpickr/flatpickr.min.css">
    @endpush
    @push('scripts')
        <script src="/assets/libs/flatpickr/flatpickr.min.js"></script>
        @if (in_array($language, ['it', 'de'], true))
            <script src="/assets/libs/flatpickr/l10n/{{ $language }}.js"></script>
        @endif
    @endpush
@endonce

<div wire:ignore>
    <input id="{{ $fieldId }}" type="text" value="{{ $answers['date'] ?? '' }}"
           class="form-control cursor-pointer"
           data-marketing-datepicker="flatpickr"
           @required($field->required)
           x-data
           x-init="flatpickr($el, {
               dateFormat: 'Y-m-d',
               minDate: @js($minimumDate),
               maxDate: @js($maximumDate),
               @if ($enabledDates !== null)
               enable: @js($enabledDates),
               @endif
               locale: @js(in_array($language, ['it', 'de'], true) ? $language : 'default'),
               disableMobile: true,
               onDayCreate: (date, dateString, instance, dayElement) => {
                   if (@js($enabledDates !== null) && !dayElement.classList.contains('flatpickr-disabled')) {
                       dayElement.classList.add('marketing-bookable-day');
                   }
               },
               onChange: (selectedDates, dateString) => $wire.set('answers.date', dateString)
           })">
</div>
