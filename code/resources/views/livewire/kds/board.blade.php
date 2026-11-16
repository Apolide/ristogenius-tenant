<div class="content order-workspace">
<div class="main-content">
@include('orders.styles')
<div class="o-fullscreen" x-data="{ async fullscreen() { if(document.fullscreenElement) await document.exitFullscreen(); else await $el.requestFullscreen(); } }">
<div class="o-toolbar">
<h1 class="o-title">KDS · {{ $departments->firstWhere('id',$departmentId)?->name ?? 'Nessun reparto produttivo' }}</h1>
<button class="ti-btn ti-btn-light" @click="fullscreen()">⛶ Schermo intero</button>@foreach($departments as $department)<a class="o-chip {{ $departmentId === $department->id ? 'active' : '' }}" href="{{ route('kds.show',$department) }}">{{ $department->name }}</a>@endforeach
</div>
@include('orders.errors')
<div class="o-board" wire:poll.3s>
@foreach(['queued'=>'Da fare','preparing'=>'In preparazione','ready'=>'Pronto','served'=>'Servito'] as $state=>$label)
<section>
<h2 class="font-semibold o-row">{{ $label }} <span class="o-badge">{{ $tickets->where('status',$state)->count() }}</span>
</h2>
@forelse($tickets->where('status',$state) as $ticket)<article class="o-ticket" wire:key="ticket-{{ $ticket->id }}-{{ $ticket->status }}" style="border-top-color:{{ ['queued'=>'#f59e0b','preparing'=>'#6366f1','ready'=>'#22c55e','served'=>'#94a3b8'][$state] }}">
<div class="o-row">
<h3 class="text-lg font-bold">Tavolo {{ $ticket->course->order->table_name }}</h3>
<span class="o-badge">{{ $ticket->course->number }}ª uscita</span>
</div>
<p class="o-muted">{{ $ticket->course->order->room_name }}</p>
@foreach($ticket->items as $item)<div class="o-line">
<strong>{{ $item->quantity }} × {{ $item->product_name }}</strong>@foreach($item->modifiers as $modifier)<p>{{ $modifier->action === 'add' ? '+' : '−' }} {{ $modifier->ingredient_name }}</p>@endforeach @if($item->notes)<p class="font-semibold mt-2">{{ $item->notes }}</p>@endif
</div>@endforeach
<p class="o-muted mt-3">Inviata {{ $ticket->sent_at->format('d/m H:i') }} · {{ $ticket->sent_at->diffForHumans() }}</p>
@if($state !== 'served')<button class="ti-btn ti-btn-primary-full w-full mt-3" wire:click="advance('{{ $ticket->id }}','{{ $state }}')" wire:loading.attr="disabled">{{ ['queued'=>'Inizia preparazione','preparing'=>'Segna pronto','ready'=>'Segna servito'][$state] }}</button>@endif
</article>@empty
<p class="o-empty">Nessuna uscita</p>@endforelse
</section>@endforeach
</div>
</div>
</div>
</div>
