<div class="content" x-data="{ productSearch: '', menuFormLanguage: 'it' }"><div class="main-content">
 @if($mode === 'list')
 <div class="mb-4 block items-center justify-between gap-4 md:flex page-header-breadcrumb"><div><h5 class="page-title text-[1.3125rem] font-medium mb-0">Menu Studio</h5><p class="mt-1 text-sm text-textmuted">Crea, organizza e pubblica menu multilingua.</p></div><button wire:click="create" class="ti-btn ti-btn-primary-full !mb-0 mt-3 md:mt-0"><i class="las la-plus"></i> Nuovo menu</button></div>
 @if(session('success'))<div class="alert alert-success mb-4">{{ session('success') }}</div>@endif
 <div class="box"><div class="box-body !p-4"><div class="mb-4 flex items-center gap-3"><div class="relative flex-1"><i class="las la-search absolute start-3 top-1/2 z-10 -translate-y-1/2 text-lg text-textmuted"></i><input wire:model.live.debounce.300ms="search" class="form-control !ps-10" placeholder="Cerca menu..."></div><span class="hidden whitespace-nowrap text-xs text-textmuted md:block">{{ $menus->count() }} menu</span></div><div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
 @forelse($menus as $item)<article class="group overflow-hidden rounded-xl border border-defaultborder bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:bg-bodybg"><div class="flex min-h-24 flex-col justify-between p-4" style="background: linear-gradient(135deg, #4338ca 0%, #0f766e 100%);"><div class="flex items-start justify-between gap-3"><span class="badge border border-white/30 bg-white/90 {{ $item->is_published ? '!text-success' : '!text-warning' }}">{{ $item->is_published ? '● Pubblicato' : '○ Bozza' }}</span><span class="text-xs font-medium !text-white/80">#{{ $loop->iteration }}</span></div><h3 class="mt-3 line-clamp-2 text-base font-semibold leading-tight !text-white">{{ $item->translatedName('it') }}</h3></div><div class="p-3"><div class="mb-3 flex min-h-7 items-center justify-between gap-3 text-xs text-textmuted"><span class="inline-flex items-center gap-1"><i class="las la-layer-group"></i> {{ $item->categories_count }} categorie</span><span class="flex gap-1">@foreach($item->enabled_languages as $language)<b class="rounded bg-light px-1.5 py-1 font-semibold !text-defaulttextcolor">{{ strtoupper($language) }}</b>@endforeach</span></div><div class="flex items-center gap-1.5"><button wire:click="openEditor('{{ $item->id }}')" class="ti-btn ti-btn-primary flex-1 justify-center !mb-0 !py-2"><i class="las la-stream"></i> Editor</button>@if($item->is_published)<a title="Anteprima" target="_blank" href="{{ route('menu.public',['language'=>$item->enabled_languages[0],'menu'=>$item]) }}" class="ti-btn ti-btn-icon ti-btn-light !mb-0"><i class="las la-eye text-lg"></i></a>@endif<button title="Impostazioni" wire:click="editMenu('{{ $item->id }}')" class="ti-btn ti-btn-icon ti-btn-light !mb-0"><i class="las la-cog text-lg"></i></button><button title="Elimina" wire:click="delete('{{ $item->id }}')" wire:confirm="Eliminare il menu?" class="ti-btn ti-btn-icon ti-btn-light !mb-0 !text-danger"><i class="las la-trash text-lg"></i></button></div></div></article>
 @empty<div class="col-span-full text-center py-16 text-textmuted">Non ci sono menu. Crea il primo menu digitale.</div>@endforelse
 </div></div></div>
 @else
 <div class="md:flex block items-center justify-between mb-6"><div><button wire:click="back" class="text-primary text-sm mb-2">← Tutti i menu</button><h5 class="page-title text-[1.3125rem] font-medium">{{ $menu->translatedName('it') }}</h5><p class="text-textmuted text-sm">Trascina concettualmente l'ordine con le frecce, aggiungi prodotti e configura i suggerimenti dal catalogo.</p></div><div class="flex gap-2">@if($menu->is_published)<a target="_blank" href="{{ route('menu.public',['language'=>$menu->enabled_languages[0],'menu'=>$menu]) }}" class="ti-btn ti-btn-light">Anteprima</a>@endif<button wire:click="publish" class="ti-btn ti-btn-primary-full">Pubblica modifiche</button></div></div>
 @if(session('success'))<div class="alert alert-success mb-4">{{ session('success') }}</div>@endif
 <div class="grid xl:grid-cols-12 gap-5">
  <aside class="xl:col-span-3 box"><div class="box-header justify-between"><h3 class="box-title">Categorie</h3></div><div class="box-body !p-3">@foreach($menu->categories as $category)<button wire:click="$set('selectedCategoryId','{{ $category->id }}')" class="w-full flex items-center gap-2 p-3 mb-1 rounded text-left {{ $selectedCategoryId === $category->id ? 'bg-primary/10 text-primary font-semibold' : 'hover:bg-light' }}"><i class="las la-grip-vertical text-textmuted"></i><span class="flex-1">{{ $category->translatedName('it') }}</span><span class="badge bg-light !text-textmuted">{{ $category->products->count() }}</span></button>@endforeach<div x-data="{open:false}" class="mt-3"><button @click="open=!open" class="ti-btn ti-btn-light w-full">+ Nuova categoria</button><div x-show="open" x-cloak class="mt-3 space-y-2"><input wire:model="newCategoryIt" class="form-control" placeholder="Nome italiano"><input wire:model="newCategoryEn" class="form-control" placeholder="Nome inglese"><button wire:click="createCategory" class="ti-btn ti-btn-primary-full w-full">Crea</button></div></div></div></aside>
  <section class="xl:col-span-5 box"><div class="box-header"><div><h3 class="box-title">{{ $menu->categories->firstWhere('id',$selectedCategoryId)?->translatedName('it') ?? 'Seleziona una categoria' }}</h3><p class="text-xs text-textmuted">Prodotti visibili nel menu</p></div></div><div class="box-body !p-3">
   @php($selectedCategory=$menu->categories->firstWhere('id',$selectedCategoryId))
   @forelse($selectedCategory?->products ?? [] as $product)<div class="border border-defaultborder rounded p-3 mb-2 flex items-center gap-3"><div class="w-11 h-11 rounded bg-primary/10 text-primary grid place-items-center"><i class="las la-utensils text-xl"></i></div><div class="flex-1"><b>{{ $product->translatedName('it') }}</b><small class="block text-textmuted">{{ Number::currency($product->pivot->menu_price ?? $product->price,'EUR','it') }}</small></div>@if($product->recommendations->count())<span class="badge bg-warning/10 !text-warning">Suggerimenti</span>@endif<div class="flex"><button wire:click="moveProduct('{{ $product->id }}','up')" class="ti-btn ti-btn-icon ti-btn-light">↑</button><button wire:click="moveProduct('{{ $product->id }}','down')" class="ti-btn ti-btn-icon ti-btn-light">↓</button><button wire:click="removeProduct('{{ $product->id }}')" class="ti-btn ti-btn-icon ti-btn-light !text-danger">×</button></div></div>@empty<div class="text-center py-12 text-textmuted">Aggiungi prodotti dal catalogo a destra.</div>@endforelse
  </div></section>
  <aside class="xl:col-span-4 box"><div class="box-header"><div><h3 class="box-title">Catalogo prodotti</h3><p class="text-xs text-textmuted">Clicca per aggiungere alla categoria</p></div></div><div class="box-body !p-3"><input x-model="productSearch" class="form-control mb-3" placeholder="Cerca prodotto...">@foreach($products as $product)<button x-show="'{{ strtolower(addslashes($product->translatedName('it'))) }}'.includes(productSearch.toLowerCase())" wire:click="addProduct('{{ $product->id }}')" @disabled(!$selectedCategoryId) class="w-full border border-defaultborder rounded p-3 mb-2 flex items-center text-left hover:border-primary"><span class="flex-1"><b>{{ $product->translatedName('it') }}</b><small class="block text-textmuted">{{ Number::currency($product->price,'EUR','it') }}</small></span><span class="text-primary text-xl">+</span></button>@endforeach<a href="{{ route('menu.products') }}" class="ti-btn ti-btn-light w-full mt-2">+ Crea prodotto nel catalogo</a></div></aside>
 </div>
 @endif
 @if($showMenuForm)
  <div class="fixed inset-0 z-[100] flex items-start justify-center overflow-y-auto bg-black/50 p-3 sm:p-4" wire:click.self="$set('showMenuForm', false)">
   <div class="box my-2 flex max-h-[calc(100dvh-1rem)] w-full max-w-5xl flex-col overflow-hidden !mb-0 sm:my-4 sm:max-h-[calc(100dvh-2rem)]">
    <div class="box-header z-10 flex shrink-0 items-center justify-between gap-4 border-b border-defaultborder bg-white dark:bg-bodybg">
     <h3 class="box-title !mb-0">{{ $menuId ? 'Impostazioni menu' : 'Nuovo menu' }}</h3>
     <button type="button" wire:click="$set('showMenuForm', false)" class="ti-btn ti-btn-icon ti-btn-light ms-auto shrink-0 !mb-0" aria-label="Chiudi"><i class="las la-times text-xl"></i></button>
    </div>

    <form wire:submit="saveMenu" class="flex min-h-0 flex-1 flex-col">
     <div class="box-body min-h-0 flex-1 space-y-5 overflow-y-auto overscroll-contain">
      <section>
       <h4 class="mb-3 font-semibold">Nome e descrizione</h4>
       <div class="-mx-1 overflow-x-auto px-1">
        <nav role="tablist" aria-label="Lingue menu" class="--prevent-on-load-init flex min-w-max gap-2 rounded-xl border border-defaultborder bg-defaultbackground p-1">
         @foreach (['it' => ['flag' => '🇮🇹', 'label' => 'Italiano'], 'en' => ['flag' => '🇬🇧', 'label' => 'English'], 'de' => ['flag' => '🇩🇪', 'label' => 'Deutsch']] as $language => $meta)
          <button type="button" role="tab" :aria-selected="menuFormLanguage === '{{ $language }}'" @click="menuFormLanguage = '{{ $language }}'" class="ti-btn !mb-0 inline-flex items-center justify-center gap-2 rounded-lg !px-4 !py-2 text-sm font-semibold transition" :class="menuFormLanguage === '{{ $language }}' ? 'ti-btn-primary-full shadow-sm' : 'ti-btn-light text-defaulttextcolor'"><span>{{ $meta['flag'] }}</span><span>{{ $meta['label'] }}</span></button>
         @endforeach
        </nav>
       </div>

       <div class="mt-3 rounded border border-defaultborder bg-defaultbackground p-4">
        <section x-cloak x-show="menuFormLanguage === 'it'" x-transition.opacity role="tabpanel" class="grid grid-cols-1 gap-4 md:grid-cols-2"><div><label for="menu-name-it" class="mb-2 block text-sm font-medium">Nome</label><input id="menu-name-it" wire:model="nameIt" class="form-control">@error('nameIt')<span class="mt-1 block text-xs text-danger">{{ $message }}</span>@enderror</div><div><label for="menu-description-it" class="mb-2 block text-sm font-medium">Descrizione</label><textarea id="menu-description-it" wire:model="descriptionIt" rows="2" class="form-control"></textarea></div></section>
        <section x-cloak x-show="menuFormLanguage === 'en'" x-transition.opacity role="tabpanel" class="grid grid-cols-1 gap-4 md:grid-cols-2"><div><label for="menu-name-en" class="mb-2 block text-sm font-medium">Nome</label><input id="menu-name-en" wire:model="nameEn" class="form-control"></div><div><label for="menu-description-en" class="mb-2 block text-sm font-medium">Descrizione</label><textarea id="menu-description-en" wire:model="descriptionEn" rows="2" class="form-control"></textarea></div></section>
        <section x-cloak x-show="menuFormLanguage === 'de'" x-transition.opacity role="tabpanel" class="grid grid-cols-1 gap-4 md:grid-cols-2"><div><label for="menu-name-de" class="mb-2 block text-sm font-medium">Nome</label><input id="menu-name-de" wire:model="nameDe" class="form-control"></div><div><label for="menu-description-de" class="mb-2 block text-sm font-medium">Descrizione</label><textarea id="menu-description-de" wire:model="descriptionDe" rows="2" class="form-control"></textarea></div></section>
       </div>
      </section>

      <section class="border-t border-defaultborder pt-4">
       <h4 class="mb-3 font-semibold">Pubblicazione e servizio</h4>
       <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]">
        <div><label for="menu-service-charge" class="mb-2 block text-sm font-medium">Costo servizio</label><div class="input-group"><span class="input-group-text">€</span><input id="menu-service-charge" wire:model="serviceCharge" type="number" min="0" step=".01" class="form-control"></div></div>
        <div><span class="mb-2 block text-sm font-medium">Lingue pubbliche</span><div class="grid grid-cols-1 gap-2 sm:grid-cols-3">@foreach(['it'=>['🇮🇹','Italiano'],'en'=>['🇬🇧','English'],'de'=>['🇩🇪','Deutsch']] as $code=>$meta)<label class="flex cursor-pointer items-center gap-3 rounded border border-defaultborder p-3"><input wire:model="enabledLanguages" value="{{ $code }}" type="checkbox" class="shrink-0"><span>{{ $meta[0] }} {{ $meta[1] }}</span></label>@endforeach</div>@error('enabledLanguages')<span class="mt-1 block text-xs text-danger">{{ $message }}</span>@enderror</div>
       </div>
      </section>

      <section class="flex flex-col gap-3 border-t border-defaultborder pt-4 md:flex-row md:flex-nowrap">
       <label class="flex min-w-0 cursor-pointer items-start gap-3 rounded border border-defaultborder p-3 md:w-1/2"><input wire:model="isPublished" type="checkbox" class="ti-switch mt-0.5 shrink-0"><span><b class="block text-sm">Pubblicato</b><small class="block text-textmuted">Rende disponibile il menu tramite il link pubblico.</small></span></label>
       <label class="flex min-w-0 cursor-pointer items-start gap-3 rounded border border-defaultborder p-3 md:w-1/2"><input wire:model="isVisible" type="checkbox" class="ti-switch mt-0.5 shrink-0"><span><b class="block text-sm">Visibile</b><small class="block text-textmuted">Mostra il menu agli ospiti nelle lingue selezionate.</small></span></label>
      </section>
     </div>

     <div class="box-footer z-10 flex shrink-0 justify-end gap-2 border-t border-defaultborder bg-white dark:bg-bodybg">
      <button type="button" wire:click="$set('showMenuForm', false)" class="ti-btn ti-btn-light !mb-0">Annulla</button>
      <button class="ti-btn ti-btn-primary-full !mb-0">Salva e apri editor</button>
     </div>
    </form>
   </div>
  </div>
 @endif
</div></div>
