<label wire:key="table-picker-{{ $table->id }}" class="flex min-w-0 cursor-pointer items-center gap-2 rounded-md border p-2 shadow-sm transition hover:border-primary hover:bg-primary/5 {{ $selected ? 'border-info bg-info/5' : '' }}">
    <input type="checkbox" wire:model="tablePickerSelection" value="{{ $table->id }}" class="form-checkbox shrink-0 accent-green-600">
    <span class="min-w-0 leading-tight">
        <span class="block truncate text-base font-bold sm:text-lg">{{ $table->name }}</span>
        <span class="block truncate text-[11px] text-textmuted">{{ $table->min_people }}–{{ $table->max_people }} pax · {{ $table->room?->name ?: 'Senza sala' }}</span>
    </span>
</label>
