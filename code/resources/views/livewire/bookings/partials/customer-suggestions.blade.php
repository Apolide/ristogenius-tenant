@if($activeSearchField === $field && count($customerSuggestions))
    <div class="absolute z-40 left-0 right-0 mt-1 max-h-64 overflow-y-auto rounded border bg-white shadow-lg dark:bg-bodybg" role="listbox">
        @foreach($customerSuggestions as $suggestion)
            <button type="button" wire:key="customer-suggestion-{{ $field }}-{{ $suggestion['id'] }}" wire:click="selectCustomer('{{ $suggestion['id'] }}')" class="block w-full border-b px-3 py-2 text-start hover:bg-primary/10">
                <strong>{{ $suggestion['name'] }}</strong><br><small>{{ $suggestion['phone'] }}{{ $suggestion['email'] ? ' · '.$suggestion['email'] : '' }}</small>
            </button>
        @endforeach
    </div>
@endif
