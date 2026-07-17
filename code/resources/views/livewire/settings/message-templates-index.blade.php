<div class="content">
    <div class="main-content">
        <div class="md:flex block items-center justify-between mb-6 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">
                    {{ __('message_settings.title') }}
                </h5>
                <nav>
                    <ol class="flex items-center whitespace-nowrap min-w-0">
                        <li class="text-[12px]">
                            <a class="flex items-center text-primary hover:text-primary" href="/">
                                {{ __('customers.home') }}
                                <i class="ti ti-chevrons-right flex-shrink-0 mx-3 overflow-visible text-textmuted rtl:rotate-180"></i>
                            </a>
                        </li>
                        <li class="text-[12px]">
                            <a href="{{ route('settings.messages') }}" class="flex items-center text-textmuted">
                                {{ __('message_settings.breadcrumb') }}
                            </a>
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="flex xl:my-auto right-content align-items-center">
                <div class="pe-1 xl:mb-0">
                    <a href="{{ route('settings.messages') }}" title="{{ __('message_settings.refresh') }}" class="ti-btn ti-btn-warning-full text-white ti-btn-icon">
                        <i class="las text-3xl la-redo-alt"></i>
                    </a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success !mb-5" role="alert">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger !mb-5" role="alert">{{ $errors->first() }}</div>
        @endif

        @if (! $editingKey)
            <div class="box">
                <div class="box-body">
                    <div class="pb-3">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('message_settings.search') }}" class="form-control">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered whitespace-nowrap min-w-full">
                            <thead>
                                <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                    <th class="border-b dark:border-defaultborder/10 text-start">{{ __('message_settings.actions') }}</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">{{ __('message_settings.message') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($templates as $templateKey => $template)
                                    <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                        <td class="whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-5">
                                                <div class="hs-tooltip ti-main-tooltip">
                                                    <button type="button"
                                                            wire:click="edit('{{ $templateKey }}')"
                                                            class="ti-btn ti-btn-icon hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm bg-warning text-white hover:bg-warning">
                                                        <i class="las text-3xl la-pen"></i>
                                                        <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">
                                                            {{ __('message_settings.edit') }}
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                            {{ $template['label'] }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                        <td colspan="2" class="text-center text-sm text-gray-500">
                                            {{ __('message_settings.empty') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            @php($firstLanguage = array_key_first($languages) ?: 'it')

            <div x-data="{ activeLanguage: '{{ $firstLanguage }}' }" class="box mt-6">
                <div class="box-header border-none">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="box-title pb-0">
                                {{ __('message_settings.edit_title', ['label' => $form['label'] ?? '']) }}
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach (($form['wildcards'] ?? []) as $wildcard)
                                    <span class="badge bg-primary/10 text-primary">{{ $wildcard }}</span>
                                @endforeach
                            </div>
                        </div>
                        <button type="button" wire:click="resetTemplate" title="{{ __('message_settings.reset') }}" class="ti-btn ti-btn-warning-full text-white ti-btn-icon">
                            <i class="las text-3xl la-redo-alt"></i>
                        </button>
                    </div>
                </div>

                <div class="box-body">
                    <form wire:submit.prevent="save">
                        <div class="-mx-4 mb-5 overflow-x-auto px-4 sm:mx-0 sm:px-0">
                            <div role="tablist" aria-label="{{ __('message_settings.languages') }}" class="flex min-w-max gap-2 rounded-xl border border-defaultborder bg-defaultbackground p-1">
                                @foreach ($languages as $languageKey => $language)
                                    <button type="button" role="tab" :aria-selected="activeLanguage === '{{ $languageKey }}'" @click="activeLanguage = '{{ $languageKey }}'"
                                            class="ti-btn !mb-0 inline-flex items-center justify-center gap-2 rounded-lg !px-4 !py-2 text-sm font-semibold transition"
                                            :class="activeLanguage === '{{ $languageKey }}' ? 'ti-btn-primary-full shadow-sm' : 'ti-btn-light text-defaulttextcolor'">
                                        <span>{{ $language['flag'] }}</span>
                                        <span>{{ $language['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        @foreach ($languages as $languageKey => $language)
                            <section x-show="activeLanguage === '{{ $languageKey }}'" x-transition.opacity role="tabpanel">
                                <div class="w-full mb-5">
                                    <label for="subject_{{ $languageKey }}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ __('message_settings.subject', ['language' => $language['label']]) }}
                                    </label>
                                    <input wire:model="form.translations.{{ $languageKey }}.subject"
                                           type="text"
                                           id="subject_{{ $languageKey }}"
                                           class="form-control"
                                           placeholder="{{ __('message_settings.subject_placeholder') }}">
                                    @error("form.translations.{$languageKey}.subject")
                                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex flex-col mb-5">
                                    <label for="message_{{ $languageKey }}" class="mb-1 font-semibold text-defaulttextcolor">
                                        {{ __('message_settings.body', ['language' => $language['label']]) }}
                                    </label>
                                    <p class="mb-2 text-sm text-textmuted">{{ __('message_settings.automatic_name') }}</p>
                                    <textarea required
                                              id="message_{{ $languageKey }}"
                                              wire:model.lazy="form.translations.{{ $languageKey }}.message"
                                              rows="10"
                                              class="form-control mt-1 block w-full sm:text-sm"
                                              placeholder="{{ __('message_settings.body_placeholder') }}"></textarea>
                                    @error("form.translations.{$languageKey}.message")
                                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex flex-col mb-5">
                                    <label for="additional_note_{{ $languageKey }}" class="mb-1 font-semibold text-defaulttextcolor">
                                        {{ __('message_settings.additional_note', ['language' => $language['label']]) }}
                                    </label>
                                    <textarea id="additional_note_{{ $languageKey }}"
                                              wire:model.lazy="form.translations.{{ $languageKey }}.additional_note"
                                              rows="6"
                                              class="form-control mt-1 block w-full sm:text-sm"
                                              placeholder="{{ __('message_settings.additional_note_placeholder') }}"></textarea>
                                    @error("form.translations.{$languageKey}.additional_note")
                                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="w-full mb-5">
                                    <label for="sms_{{ $languageKey }}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ __('message_settings.sms', ['language' => $language['label']]) }}
                                    </label>
                                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">{{ __('message_settings.automatic_name') }}</p>
                                    <input wire:model="form.translations.{{ $languageKey }}.sms"
                                           type="text"
                                           id="sms_{{ $languageKey }}"
                                           maxlength="160"
                                           class="form-control"
                                           placeholder="{{ __('message_settings.sms_placeholder') }}">
                                    @error("form.translations.{$languageKey}.sms")
                                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </section>
                        @endforeach

                        <div class="flex gap-3">
                            <button wire:target="save" wire:loading.attr="disabled" type="submit" class="ti-btn ti-btn-primary-full ti-btn-wave me-[0.375rem]">
                                {{ __('message_settings.save') }}
                            </button>
                            <button wire:click="abort" type="button" class="ti-btn ti-btn-light mr-3">
                                {{ __('message_settings.cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
