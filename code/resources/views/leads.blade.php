@extends('layouts.app')

@section('content')
<div class="content">
  <div class="main-content">

    <div class="md:flex block items-center justify-between mb-6 mt-[2rem] page-header-breadcrumb">
      <div class="my-auto">
        <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">Leads</h5>
        <nav>
          <ol class="flex items-center whitespace-nowrap min-w-0">
            <li class="text-[12px]">
              <a class="flex items-center text-primary hover:text-primary" href="{{ route('leads.index') }}">
                Leads
                <i class="ti ti-chevrons-right flex-shrink-0 mx-3 overflow-visible text-textmuted rtl:rotate-180"></i>
              </a>
            </li>
            <li class="text-[12px]">
              <span class="flex items-center text-textmuted">Inbox</span>
            </li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid">
      <div class="main-mail-container !p-2 gap-x-2 flex">

        {{-- LEFT: filters --}}
        <div class="mail-navigation border dark:border-defaultborder/10">
          <div class="grid items-start !p-4 border-b dark:border-defaultborder/10">
            <a href="{{ route('leads.index') }}"
               class="ti-btn !bg-success text-white flex items-center justify-center !font-medium">
              <i class="ri-filter-3-line text-[1rem] align-middle"></i>
              All Leads
            </a>
          </div>

          <div class="hs-collapse-toggle p-2 inline-flex justify-center items-center gap-2 rounded-lg !border-0 font-medium bg-white dark:bg-bodybg text-gray-700 !shadow-none align-middle">
            <ul class="list-none mail-main-nav !border-0 !text-[0.813rem] overflow-scroll" id="mail-main-nav">

              <li class="!px-0 !pt-0">
                <span class="text-[.6875rem] text-textmuted opacity-[0.7] font-semibold">LEAD STATUS</span>
              </li>

              @php
                $statuses = ['new' => 'New', 'open' => 'Open', 'won' => 'Won', 'lost' => 'Lost'];
              @endphp

              @foreach($statuses as $key => $label)
                @php $active = ($status === $key); @endphp
                <li class="mail-type {{ $active ? 'active' : '' }}">
                  <a href="{{ route('leads.index', ['status' => $key]) }}">
                    <div class="flex items-center">
                      <span class="me-2 leading-none">
                        <i class="ri-flag-line align-middle text-[.875rem]"></i>
                      </span>
                      <span class="flex-grow whitespace-nowrap">{{ $label }}</span>
                      <span class="badge bg-primary/10 text-primary !rounded-full">
                        {{ $statusCounts[$key] ?? 0 }}
                      </span>
                    </div>
                  </a>
                </li>
              @endforeach

              <li class="px-0 mt-2">
                <span class="text-[.6875rem] text-textmuted opacity-[0.7] font-semibold">ACTIONS</span>
              </li>
              <li>
                <a href="{{ route('leads.index') }}">
                  <div class="flex items-center">
                    <span class="me-2 leading-none"><i class="ri-refresh-line align-middle text-[.875rem]"></i></span>
                    <span class="flex-grow whitespace-nowrap">Refresh</span>
                  </div>
                </a>
              </li>

            </ul>
          </div>
        </div>

        {{-- MIDDLE: leads list --}}
        <div class="total-mails border dark:border-defaultborder/10">
          <div class="!p-4 flex items-center border-b dark:border-defaultborder/10">
            <div class="flex-grow">
              <h6 class="font-semibold mb-0 text-[1rem] !text-defaulttextcolor dark:!text-defaulttextcolor/70">
                Leads {{ $status ? '— '.$status : '' }}
              </h6>
              <p class="text-[.75rem] text-textmuted mb-0">
                {{ $leads->total() }} results
              </p>
            </div>
          </div>

          <div class="p-4">
            <form method="GET" action="{{ route('leads.index') }}" class="input-group">
              @if($status)
                <input type="hidden" name="status" value="{{ $status }}">
              @endif
              <input type="text" name="q" value="{{ request('q') }}"
                     class="form-control !bg-light !border-0 !rounded-s-md"
                     placeholder="Search lead (name/email)...">
              <button class="ti-btn ti-btn-light !rounded-s-none !mb-0" type="submit">
                <i class="ri-search-line text-textmuted"></i>
              </button>
            </form>
          </div>

          <div class="mail-messages" id="mail-messages">
            <ul class="list-none mb-0 mail-messages-container text-defaulttextcolor text-defaultsize">

              @foreach($leads as $lead)
                @php
                  $contact = $lead->contact;
                  $conv = $lead->conversations->first(); // già ordinata dal controller
                  $isActive = $activeLead && $activeLead->id == $lead->id;
                  $latest = $lead->latestMessage;
                  $preview = '';
                  if ($latest && is_array($latest->payload)) {
                    $preview = $latest->payload['raw'] ?? $latest->payload['body'] ?? $latest->payload['text'] ?? '';
                  }
                  $preview = trim($preview);
                  if (mb_strlen($preview) > 120) $preview = mb_substr($preview, 0, 117).'...';
                @endphp

                <li class="{{ $isActive ? 'active' : '' }} !border-t-0 !border-x-0">
                  <div class="flex items-start">
                    <div class="flex-grow">
                      <a href="{{ route('leads.index', [
                          'status' => $status ?: null,
                          'lead' => $lead->id,
                          'conversation' => $conv?->id
                        ]) }}">
                        <p class="mb-1 text-[0.75rem] {{ $isActive ? 'font-semibold' : '' }}">
                          {{ $contact?->name ?? 'Unknown contact' }}
                          <span class="ltr:float-right rtl:float-left text-textmuted font-normal text-[.6875rem]">
                            {{ optional(optional($latest)->sent_at)->format('d M H:i') }}
                          </span>
                        </p>
                      </a>

                      <p class="mail-msg mb-0">
                        <span class="block mb-0 {{ $isActive ? 'font-semibold' : '' }}">
                          {{ $lead->title ?? ($conv?->subject ?? 'Conversation') }}
                        </span>
                        <span class="text-[.6875rem] text-textmuted text-wrap">
                          {{ $preview ?: ($contact?->email ?? '') }}
                        </span>
                      </p>

                      <div class="mt-1">
                        <span class="badge bg-success/10 text-success !rounded-full">{{ $lead->status ?? 'open' }}</span>
                        @if($contact?->lang)
                          <span class="badge bg-primary/10 text-primary !rounded-full">{{ $contact->lang }}</span>
                        @endif
                        @if($latest?->channel)
                          <span class="badge bg-secondary/10 text-secondary !rounded-full">{{ $latest->channel }}</span>
                        @endif
                      </div>
                    </div>
                  </div>
                </li>
              @endforeach

              @if($leads->isEmpty())
                <li class="!border-x-0 !border-t-0 p-4 text-textmuted">
                  No leads found.
                </li>
              @endif

            </ul>
          </div>

          <div class="p-4">
            {{ $leads->links() }}
          </div>
        </div>

        {{-- RIGHT: conversation --}}
        <div class="mails-information border dark:border-defaultborder/10 text-defaulttextcolor text-defaultsize">

          @if(!$activeLead || !$activeConversation)
            <div class="p-6">
              <h6 class="font-semibold text-[1rem] mb-2">Select a lead</h6>
              <p class="text-textmuted text-[.875rem] mb-0">
                Choose a lead from the list to view its conversation and messages.
              </p>
            </div>
          @else
            @php $contact = $activeLead->contact; @endphp

            <div class="mail-info-header flex flex-wrap gap-2 items-center">
              <div class="flex-grow">
                <h6 class="mb-0 font-semibold text-[1rem] dark:text-defaulttextcolor/70">
                  {{ $contact?->name ?? 'Unknown contact' }}
                </h6>
                <span class="text-textmuted text-[0.75rem]">{{ $contact?->email }}</span>
              </div>

              <div class="mail-action-icons">
                <span class="badge bg-success/10 text-success !rounded-full">{{ $activeLead->status }}</span>
                <span class="badge bg-primary/10 text-primary !rounded-full">Thread #{{ $activeConversation->id }}</span>
              </div>
            </div>

            <div class="mail-info-body dark:!border-defaultborder/10 p-6" id="mail-info-body">
              <div class="sm:flex block items-center justify-between mb-6">
                <div>
                  <p class="text-[1.1rem] font-semibold mb-0">
                    {{ $activeConversation->subject ?? ($activeLead->title ?? 'Conversation') }}
                  </p>
                  <p class="text-[.75rem] text-textmuted mb-0">
                    Last update: {{ optional($activeConversation->last_update)->format('d M Y H:i') }}
                  </p>
                </div>
              </div>

              <div class="main-mail-content mb-6 space-y-4">
                @foreach($messages as $m)
                  @php
                    $isInbound = ($m->direction === 'inbound');
                    $raw = '';
                    if (is_array($m->payload)) {
                      $raw = $m->payload['raw'] ?? $m->payload['body'] ?? $m->payload['text'] ?? '';
                    } else {
                      $raw = (string)$m->payload;
                    }
                    $raw = trim($raw);
                  @endphp

                  <div class="p-4 rounded-md border dark:border-defaultborder/10 {{ $isInbound ? '' : 'bg-primary/5' }}">
                    <div class="flex items-center justify-between mb-2">
                      <span class="text-[.75rem] font-semibold">
                        {{ $isInbound ? 'CLIENT' : 'AGENT' }}
                        <span class="text-textmuted font-normal">— {{ $m->channel ?? $m->source }}</span>
                      </span>
                      <span class="text-[.6875rem] text-textmuted">
                        {{ optional($m->sent_at)->format('d M H:i') }}
                      </span>
                    </div>

                    <pre class="whitespace-pre-wrap text-[.85rem] leading-relaxed text-defaulttextcolor dark:text-defaulttextcolor/70">{{ $raw }}</pre>
                  </div>
                @endforeach

                @if($messages->isEmpty())
                  <p class="text-textmuted mb-0">No messages in this conversation yet.</p>
                @endif
              </div>

              <div class="mb-4">
                <span class="text-[.875rem] font-semibold">
                  <i class="ri-reply-all-line me-1 align-middle inline-block"></i>Reply (coming next)
                </span>
                <p class="text-[.75rem] text-textmuted mb-0">
                  In this version we show the thread. Next step: reply actions + creating a draft (IMAP) and linking it.
                </p>
              </div>

              {{-- Placeholder reply box --}}
              <div class="mail-reply dark:border-defaultborder/10 p-4 border rounded-md">
                <textarea class="form-control" rows="6" placeholder="Reply editor will be wired to draft creation..."></textarea>
                <div class="mt-3 flex gap-2">
                  <button class="ti-btn ti-btn-secondary-full" type="button" disabled>
                    Create draft
                  </button>
                  <button class="ti-btn ti-btn-danger-full" type="button" disabled>
                    Send
                  </button>
                </div>
              </div>

            </div>
          @endif

        </div>
      </div>
    </div>

  </div>
</div>
@endsection
