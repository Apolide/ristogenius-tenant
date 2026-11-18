<div class="content order-workspace">
<div class="main-content">
@include('orders.styles')
@include('orders.workspace-styles')
<x-orders.workspace :title="__('orders.table').' '.$order->table_name" icon="la-utensils">
    <x-slot:actions><a class="ti-btn ti-btn-light !m-0" href="{{ route('orders.index') }}"><i class="las la-arrow-left" aria-hidden="true"></i> {{ __('cash_desk.dining_room') }}</a>@if(auth()->user()->can(\App\Services\Personnel\PersonnelPermissionsService::CASH_REGISTER))<a class="ti-btn ti-btn-primary-full !m-0" href="{{ route('cash-desk.show',$order) }}"><i class="las la-cash-register" aria-hidden="true"></i> {{ __('orders.editor.go_to_cash_desk') }}</a>@endif</x-slot:actions>
    <x-slot:stats>{{ $order->room_name }} · {{ $order->service_date->format('d/m/Y') }} · {{ ['open'=>__('cash_desk.open'),'paid'=>__('cash_desk.paid'),'cancelled'=>__('cash_desk.cancelled')][$order->status] }}</x-slot:stats>

@include('orders.errors')
<div class="o-grid">
<section class="box"><div class="box-body">
<div class="o-toolbar">
<select aria-label="{{ __('orders.editor.menu') }}" class="form-control" wire:model.live="menuId">@forelse($menus as $menu)<option value="{{ $menu->id }}">{{ $menu->translatedName() }}</option>@empty
<option>{{ __('orders.editor.no_visible_menus') }}</option>@endforelse
</select>
<input class="form-control" type="search" placeholder="{{ __('orders.editor.search_for_a_product') }}" aria-label="{{ __('orders.editor.search_product') }}" wire:model.live.debounce.300ms="search">
</div>
<div class="o-categories">@foreach($categories as $category)<button class="o-chip {{ $categoryId === $category->id ? 'active' : '' }}" wire:click="$set('categoryId','{{ $category->id }}')">{{ $category->translatedName() }}</button>@endforeach
</div>
<div class="o-products">@forelse($products as $productRow)<button class="o-product" wire:click="chooseProduct('{{ $productRow->id }}')" @disabled($order->status !== 'open')>
<strong>{{ $productRow->translatedName() }}</strong>
<span class="text-primary font-semibold">{{ number_format($productRow->pivot->menu_price ?? $productRow->price,2,',','.') }} € <i class="las la-plus float-end" aria-hidden="true"></i>
</span>
</button>@empty
<p class="o-empty">{{ __('orders.editor.no_products_available') }}</p>@endforelse
</div>
</div></section>
<section class="box"><div class="box-body">
<div class="o-row">
<h2 class="font-semibold text-lg">{{ __('orders.editor.order') }}</h2>@if($order->status === 'open')<button class="ti-btn ti-btn-primary-full" wire:click="addCourse" wire:loading.attr="disabled"><i class="las la-plus" aria-hidden="true"></i> {{ __('orders.editor.add_course') }}</button>@endif
</div>
<form wire:submit="updateCovers" class="service-form-grid mt-3">
<label>{{ __('orders.covers') }} <input aria-label="{{ __('orders.covers') }}" type="number" min="1" max="1000" style="width:80px" class="form-control" wire:model="covers" @disabled($order->status !== 'open')>
</label>@if($order->status === 'open')<button class="ti-btn ti-btn-light"><i class="las la-sync" aria-hidden="true"></i> {{ __('cash_desk.reconciliation.refresh') }}</button>@endif
<span class="o-muted">{{ number_format($order->cover_price_cents/100,2,',','.') }} {{ __('orders.editor.euros_per_person') }}</span>
</form>
<div wire:poll.5s>
@foreach($order->courses as $course)
@php($editable = $order->status === 'open' && !$course->rejected_at && !$course->tickets->contains(fn($ticket) => !in_array($ticket->status,['queued','cancelled'])))
<div class="o-line" wire:key="course-{{ $course->id }}">
<div class="o-row">
<div class="flex items-center gap-2">@if(!$course->sent_at && !$course->submission_key && $order->status === 'open')<input type="radio" aria-label="{{ __('orders.editor.select_course_number', ['number' => $course->number]) }}" wire:model.live="courseId" value="{{ $course->id }}">@endif
<strong>{{ __('orders.course_number', ['number' => $course->number]) }}</strong>@if($course->customer_name)<span class="o-badge">{{ __('orders.editor.customer') }} {{ $course->customer_name }}</span>@endif
</div>@if($course->rejected_at)<span class="o-badge">{{ __('orders.editor.rejected') }}</span>@elseif($course->sent_at)<span class="o-badge">{{ __('orders.editor.sent') }} {{ $course->sent_at->format('H:i') }}</span>@elseif($order->status === 'open')<button class="ti-btn ti-btn-primary-full" wire:click="sendCourse('{{ $course->id }}')" wire:loading.attr="disabled" @disabled($course->items->isEmpty())><i class="las la-paper-plane" aria-hidden="true"></i> {{ __('orders.editor.send_course') }}</button>@if($course->submission_key)<button class="ti-btn ti-btn-danger" wire:click="rejectCourse('{{ $course->id }}')" wire:confirm="{{ __('orders.editor.confirm_customer_course_rejection') }}">{{ __('orders.editor.reject') }}</button>@endif @endif
</div>
@if($course->customer_notes)<p class="o-muted">{{ __('orders.editor.customer_notes') }} {{ $course->customer_notes }}</p>@endif
@forelse($course->items as $item)<div class="o-line" wire:key="item-{{ $item->id }}">
<div class="o-row">
<strong>{{ $item->quantity }} × {{ $item->product_name }}</strong>
<span>{{ number_format($item->unit_price_cents*$item->quantity/100,2,',','.') }} €</span>
</div>
@foreach($item->modifiers as $modifier)<p class="o-muted"><i @class(['las', 'la-plus' => $modifier->action === 'add', 'la-minus' => $modifier->action !== 'add']) aria-hidden="true"></i><span class="sr-only">{{ $modifier->action === 'add' ? __('orders.editor.add_ingredients') : __('orders.editor.remove_recipe_ingredients') }}</span> {{ $modifier->ingredient_name }} ({{ number_format($modifier->price_cents/100,2,',','.') }} €)</p>@endforeach
@if($item->notes)<p class="o-muted">{{ __('cash_desk.print.notes') }} {{ $item->notes }}</p>@endif
<div class="o-row mt-2">
<span class="o-badge">{{ $item->statusLabel() }}</span>@if($editable)<div>
<button class="ti-btn ti-btn-warning-full !p-1" wire:click="editItem('{{ $item->id }}')"><i class="las la-pen" aria-hidden="true"></i> {{ __('orders.editor.edit') }}</button>
<button class="ti-btn ti-btn-danger !p-1" wire:click="removeItem('{{ $item->id }}')" wire:confirm="{{ __('orders.editor.remove_this_product_from_the_order') }}"><i class="las la-trash" aria-hidden="true"></i> {{ __('orders.editor.remove') }}</button>
</div>@endif
</div>
</div>@empty
<p class="o-muted mt-3">{{ __('orders.editor.select_this_course_and_add_products_from_the_menu') }}</p>@endforelse
</div>
@endforeach
<div class="o-row mt-5">
<strong>{{ __('orders.editor.total_including_covers') }}</strong>
<strong class="text-xl">{{ number_format($order->totalCents()/100,2,',','.') }} €</strong>
</div>
</div>
</div></section>
</div>
@if($productModal)
<div class="o-modal">
<form wire:submit="saveItem" class="o-dialog">
<h2 class="text-xl font-semibold">{{ $editingItemId ? __('orders.editor.edit_product') : $product?->translatedName() }}</h2>
@include('orders.errors')
<label class="o-field">
<span>{{ __('cash_desk.assign_items.quantity') }}</span>
<input type="number" min="1" max="1000" wire:model="quantity" class="form-control">
</label>
<label class="o-field">
<span>{{ __('orders.editor.department_notes') }}</span>
<textarea wire:model="notes" maxlength="2000" class="form-control w-full" rows="3">
</textarea>
</label>
@if($product)
@if($product->ingredients->isNotEmpty())<fieldset class="o-field">
<legend class="font-semibold mb-2">{{ __('orders.editor.remove_recipe_ingredients') }}</legend>@foreach($product->ingredients as $ingredient)<label class="block mb-2">
<input class="form-check-input" type="checkbox" value="{{ $ingredient->id }}" wire:model="removed"> {{ $ingredient->translatedName() }} <span class="o-muted">{{ number_format($ingredient->remove_price,2,',','.') }} €</span>
</label>@endforeach
</fieldset>@endif
<fieldset class="o-field">
<legend class="font-semibold mb-2">{{ __('orders.editor.add_ingredients') }}</legend>
<div style="max-height:200px;overflow:auto">@foreach($ingredients as $ingredient)<label class="block mb-2">
<input class="form-check-input" type="checkbox" value="{{ $ingredient->id }}" wire:model="added"> {{ $ingredient->translatedName() }} <span class="o-muted">+{{ number_format($ingredient->add_price,2,',','.') }} €</span>
</label>@endforeach
</div>
</fieldset>
@endif
<div class="o-row mt-4">
<button type="button" class="ti-btn ti-btn-light" wire:click="$set('productModal',false)"><i class="las la-times" aria-hidden="true"></i> {{ __('cash_desk.checkout.close') }}</button>
<button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled"><i class="las la-save" aria-hidden="true"></i> {{ $editingItemId ? __('orders.editor.save') : __('orders.editor.add_to_selected_course') }}</button>
</div>
</form>
</div>
@endif
</x-orders.workspace>
</div>
</div>
