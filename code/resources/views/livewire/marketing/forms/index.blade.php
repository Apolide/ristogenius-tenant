<div class="content">
    <div class="main-content">
        <div class="flex items-center justify-between mb-6">
            <h5 class="page-title">{{ __('marketing_forms.title') }}</h5>
            <div class="flex gap-3">
                <a class="ti-btn ti-btn-info-full ti-btn-icon text-white" href="{{ route('marketing.forms.submissions') }}"><i class="mdi mdi-eye"></i></a>
                <a class="ti-btn ti-btn-info-full ti-btn-icon text-white" href="{{ route('marketing.forms.create') }}"><i class="las text-3xl la-plus"></i></a>
            </div>
        </div>

        @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

        <div class="box">
            <div class="box-body">
                <input wire:model.live.debounce.300ms="search" class="form-control mb-3" placeholder="{{ __('Cerca form') }}">
                <div class="table-responsive">
                    <table class="table table-bordered min-w-full">
                        <thead><tr><th>{{ __('Azioni') }}</th><th>{{ __('Titolo') }}</th><th>{{ __('Tipo') }}</th><th>Link</th><th>{{ __('Stato') }}</th></tr></thead>
                        <tbody>
                            @forelse ($forms as $form)
                                <tr>
                                    <td><div class="flex gap-3">
                                        <a class="ti-btn ti-btn-icon bg-warning text-white" href="{{ route('marketing.forms.edit', $form) }}"><i class="las la-pen"></i></a>
                                        @unless($form->isPrimaryBookingForm())
                                            <button wire:click="delete('{{ $form->id }}')" wire:confirm="{{ __('Eliminare il form?') }}" class="ti-btn ti-btn-icon bg-danger text-white"><i class="las la-trash"></i></button>
                                        @endunless
                                    </div></td>
                                    <td>{{ $form->title() }}</td>
                                    <td>{{ $form->type }}</td>
                                    <td>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($form->enabled_languages as $language)
                                                @php($meta = $languageMeta[$language] ?? ['flag' => '🌐', 'label' => strtoupper($language)])
                                                <a href="{{ route('marketing.forms.public', ['language' => $language, 'form' => $form->slug]) }}" target="_blank" rel="noopener noreferrer"
                                                   class="ti-btn ti-btn-light ti-btn-sm" title="{{ $meta['label'] }}" aria-label="{{ __('Apri il form in :language', ['language' => $meta['label']]) }}">
                                                    <span class="text-xl">{{ $meta['flag'] }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>{{ $form->is_active ? __('Attivo') : __('Disattivo') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">{{ __('Nessun form.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $forms->links() }}
            </div>
        </div>
    </div>
</div>
