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
                <input wire:model.live.debounce.300ms="search" class="form-control mb-3" placeholder="Cerca form">
                <div class="table-responsive">
                    <table class="table table-bordered min-w-full">
                        <thead><tr><th>Azioni</th><th>Titolo</th><th>Tipo</th><th>Link</th><th>Stato</th></tr></thead>
                        <tbody>
                            @forelse ($forms as $form)
                                <tr>
                                    <td><div class="flex gap-3">
                                        <a class="ti-btn ti-btn-icon bg-warning text-white" href="{{ route('marketing.forms.edit', $form) }}"><i class="las la-pen"></i></a>
                                        <button wire:click="delete('{{ $form->id }}')" wire:confirm="Eliminare il form?" class="ti-btn ti-btn-icon bg-danger text-white"><i class="las la-trash"></i></button>
                                    </div></td>
                                    <td>{{ $form->title() }}</td>
                                    <td>{{ $form->type }}</td>
                                    <td>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($form->enabled_languages as $language)
                                                @php($meta = $languageMeta[$language] ?? ['flag' => '🌐', 'label' => strtoupper($language)])
                                                <a href="{{ route('marketing.forms.public', [$language, $form]) }}" target="_blank" rel="noopener noreferrer"
                                                   class="ti-btn ti-btn-light ti-btn-sm" title="{{ $meta['label'] }}" aria-label="Apri il form in {{ $meta['label'] }}">
                                                    <span class="text-xl">{{ $meta['flag'] }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>{{ $form->is_active ? 'Attivo' : 'Disattivo' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">Nessun form.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $forms->links() }}
            </div>
        </div>
    </div>
</div>
