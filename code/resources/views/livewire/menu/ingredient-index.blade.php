<div class="content" x-data>
 <div class="main-content">
  <div class="md:flex block items-center justify-between mb-6 page-header-breadcrumb"><div><h5 class="page-title text-[1.3125rem] font-medium mb-0">{{ __('menu.common.ingredients') }}</h5><p class="text-textmuted text-sm mt-1">{{ __('menu.ingredients.availability_units_and_modifier_prices') }}</p></div><button wire:click="create" class="ti-btn ti-btn-primary-full"><i class="las la-plus"></i> {{ __('menu.ingredients.new_ingredient') }}</button></div>
  @if(session('success'))<div class="alert alert-success mb-4">{{ session('success') }}</div>@endif
  <div class="box"><div class="box-body"><div class="flex gap-3 mb-5"><input wire:model.live.debounce.300ms="search" class="form-control flex-1" placeholder="{{ __('menu.ingredients.search_ingredients') }}"><select class="form-control !w-auto"><option>{{ __('menu.ingredients.all_ingredients') }}</option><option>{{ __('menu.ingredients.tracked') }}</option></select></div>
   <div class="table-responsive"><table class="table table-bordered min-w-full"><thead><tr><th>{{ __('menu.ingredients.ingredient') }}</th><th>{{ __('menu.ingredients.unit') }}</th><th>{{ __('menu.ingredients.availability') }}</th><th>{{ __('menu.ingredients.used_in') }}</th><th>{{ __('menu.ingredients.modifier') }}</th><th>{{ __('menu.common.actions') }}</th></tr></thead><tbody>
    @forelse($ingredients as $ingredient)<tr><td><b>{{ $ingredient->translatedName() }}</b><div class="text-xs text-textmuted">{{ $ingredient->translatedName('en') }}</div></td><td>{{ $ingredient->unit }}</td><td><span class="badge {{ $ingredient->tracked ? 'bg-success/10 !text-success' : 'bg-light !text-textmuted' }}">{{ $ingredient->tracked ? number_format($ingredient->stock_quantity, 3, ',', '.').' '.$ingredient->unit : __('menu.ingredients.not_tracked') }}</span></td><td>{{ $ingredient->products_count }} {{ __('menu.ingredients.products') }}</td><td>{{ $ingredient->add_price > 0 ? '+ '.Number::currency($ingredient->add_price, 'EUR', app()->getLocale()) : __('menu.ingredients.included') }}</td><td><div class="flex gap-2"><button wire:click="edit('{{ $ingredient->id }}')" class="ti-btn ti-btn-icon bg-warning text-white"><i class="las la-pen"></i></button><button wire:click="delete('{{ $ingredient->id }}')" wire:confirm="{{ __('menu.ingredients.delete_this_ingredient') }}" class="ti-btn ti-btn-icon bg-danger text-white"><i class="las la-trash"></i></button></div></td></tr>
    @empty<tr><td colspan="6" class="text-center text-textmuted py-10">{{ __('menu.ingredients.no_ingredients_create_one_to_get_started') }}</td></tr>@endforelse
   </tbody></table></div><div class="mt-4">{{ $ingredients->links() }}</div></div></div>
  @if($showForm)
      <div class="fixed inset-0 z-[1000] flex items-start justify-center overflow-y-auto bg-black/50 px-3 pb-3 pt-20 sm:px-4 sm:pb-4" wire:click.self="$set('showForm', false)">
          <div x-data="{ activeLanguage: '{{ $defaultLanguage }}' }" class="box flex max-h-[calc(100dvh-6rem)] w-full max-w-5xl flex-col overflow-hidden !mb-0">
              <div class="box-header z-10 flex shrink-0 items-center justify-between gap-4 border-b border-defaultborder bg-white dark:bg-bodybg">
                  <h3 class="box-title !mb-0">{{ $editingId ? __('menu.ingredients.edit_ingredient') : __('menu.ingredients.new_ingredient') }}</h3>
                  <button type="button" wire:click="$set('showForm', false)" class="ti-btn ti-btn-icon ti-btn-light ms-auto shrink-0 !mb-0" aria-label="{{ __('menu.common.close') }}">
                      <i class="las la-times text-xl"></i>
                  </button>
              </div>

              <form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
                  <div class="box-body min-h-0 flex-1 space-y-5 overflow-y-auto overscroll-contain">
                      <section>
                          <div class="mb-3 flex items-center justify-between gap-3">
                              <div>
                                  <h4 class="font-semibold">{{ __('menu.common.ingredient_name') }}</h4>
                              </div>
                          </div>

                          <div class="-mx-1 overflow-x-auto px-1">
                              <nav role="tablist" aria-label="{{ __('menu.ingredients.ingredient_languages') }}" class="--prevent-on-load-init flex min-w-max gap-2 rounded-xl border border-defaultborder bg-defaultbackground p-1">
                                  @foreach($languageMeta as $language => $meta)
                                      <button type="button" role="tab"
                                              :aria-selected="activeLanguage === '{{ $language }}'"
                                              @click="activeLanguage = '{{ $language }}'"
                                              class="ti-btn !mb-0 inline-flex items-center justify-center gap-2 rounded-lg !px-4 !py-2 text-sm font-semibold transition"
                                              :class="activeLanguage === '{{ $language }}' ? 'ti-btn-primary-full shadow-sm' : 'ti-btn-light text-defaulttextcolor'">
                                          <x-language-label :meta="$meta" />
                                      </button>
                                  @endforeach
                              </nav>
                          </div>

                          <div class="mt-3 rounded border border-defaultborder bg-defaultbackground p-3">
                              @foreach($languageMeta as $language => $meta)
<div x-cloak x-show="activeLanguage === '{{ $language }}'" x-transition.opacity role="tabpanel">
                                  <label for="ingredient-name-{{ $language }}" class="sr-only">{{ __('menu.common.name') }} ({{ $meta['label'] }})</label>
                                  <input id="ingredient-name-{{ $language }}" wire:model="name.{{ $language }}" class="form-control" autocomplete="off">
                                  @error('name.'.$language)<span class="mt-1 block text-xs text-danger">{{ $message }}</span>@enderror
                              </div>
@endforeach
                          </div>
                      </section>

                      <section class="border-t border-defaultborder pt-4">
                          <h4 class="mb-3 font-semibold">{{ __('menu.ingredients.availability_and_units') }}</h4>
                          <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                              <div>
                                  <label for="ingredient-unit" class="mb-2 block text-sm font-medium">{{ __('menu.ingredients.unit_of_measurement') }}</label>
                                  <select id="ingredient-unit" wire:model="unit" class="form-control">
                                      <option value="kg">{{ __('menu.ingredients.kilograms_kg') }}</option>
                                      <option value="g">{{ __('menu.ingredients.grams_g') }}</option>
                                      <option value="l">{{ __('menu.ingredients.liters_l') }}</option>
                                      <option value="ml">{{ __('menu.ingredients.milliliters_ml') }}</option>
                                      <option value="pz">{{ __('menu.ingredients.pieces_pcs') }}</option>
                                  </select>
                              </div>
                              <div>
                                  <label for="ingredient-stock" class="mb-2 block text-sm font-medium">{{ __('menu.ingredients.available_quantity') }}</label>
                                  <input id="ingredient-stock" wire:model="stockQuantity" type="number" min="0" step=".001" class="form-control">
                              </div>
                              <div>
                                  <label for="ingredient-minimum" class="mb-2 block text-sm font-medium">{{ __('menu.ingredients.minimum_stock_threshold') }}</label>
                                  <input id="ingredient-minimum" wire:model="minimumQuantity" type="number" min="0" step=".001" class="form-control">
                                  <p class="mt-1 text-xs text-textmuted">{{ __('menu.ingredients.ristopilot_flags_the_ingredient_when_the_quantity_drops_below_this') }}</p>
                              </div>
                          </div>
                      </section>

                      <section class="border-t border-defaultborder pt-4">
                          <h4 class="mb-1 font-semibold">{{ __('menu.ingredients.modifier_prices') }}</h4>
                          <p class="mb-3 text-xs text-textmuted">{{ __('menu.ingredients.amounts_applied_when_the_customer_adds_or_removes_the_ingredient') }}</p>
                          <div class="flex flex-row flex-nowrap items-start gap-4">
                              <div class="min-w-0 w-1/2">
                                  <label for="ingredient-add-price" class="mb-2 block text-sm font-medium">{{ __('menu.ingredients.addition_price') }}</label>
                                  <div class="input-group">
                                      <span class="input-group-text">€</span>
                                      <input id="ingredient-add-price" wire:model="addPrice" type="number" step=".01" class="form-control">
                                  </div>
                              </div>
                              <div class="min-w-0 w-1/2">
                                  <label for="ingredient-remove-price" class="mb-2 block text-sm font-medium">{{ __('menu.ingredients.removal_price') }}</label>
                                  <div class="input-group">
                                      <span class="input-group-text">€</span>
                                      <input id="ingredient-remove-price" wire:model="removePrice" type="number" step=".01" class="form-control">
                                  </div>
                              </div>
                          </div>
                      </section>

                      <section class="flex flex-col gap-3 border-t border-defaultborder pt-4 md:flex-row md:flex-nowrap">
                          <label class="flex min-w-0 cursor-pointer items-start gap-3 rounded border border-defaultborder p-3 md:w-1/2">
                              <input wire:model="tracked" type="checkbox" class="ti-switch mt-0.5 shrink-0">
                              <span><b class="block text-sm">{{ __('menu.ingredients.track_stock') }}</b><small class="block text-textmuted">{{ __('menu.ingredients.deduct_stock_through_orders') }}</small></span>
                          </label>
                          <label class="flex min-w-0 cursor-pointer items-start gap-3 rounded border border-defaultborder p-3 md:w-1/2">
                              <input wire:model="isFrozen" type="checkbox" class="ti-switch mt-0.5 shrink-0">
                              <span><b class="block text-sm">{{ __('menu.ingredients.frozen_product') }}</b><small class="block text-textmuted">{{ __('menu.ingredients.show_the_indication_on_the_menu') }}</small></span>
                          </label>
                      </section>
                  </div>

                  <div class="box-footer z-10 flex shrink-0 justify-end gap-2 border-t border-defaultborder bg-white dark:bg-bodybg">
                      <button type="button" wire:click="$set('showForm', false)" class="ti-btn ti-btn-light !mb-0">{{ __('menu.common.cancel') }}</button>
                      <button class="ti-btn ti-btn-primary-full !mb-0" wire:loading.attr="disabled">{{ __('menu.ingredients.save_ingredient') }}</button>
                  </div>
              </form>
          </div>
      </div>
  @endif
 </div>
</div>
