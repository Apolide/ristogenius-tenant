<div>
    @if($isVisible)
    <div class="box">
        <div class="box-header border-none">
            <div class="flex justify-between">
                <div>
                    <div class="box-title pb-0">DETTAGLIO {{$firstname}} {{$lastname}}</div>
                    <button wire:click="back()" type="button" class="ti-btn ti-btn-light mt-3">
                        Indietro
                    </button>
                    {{-- <p class="text-xs text-gray-500 font-normal">Gestisci i tuoi prodotti qui.</p> --}}
                </div>
                
 
                
            </div>
        </div> 
        <div class="box-body">

            <div class="table-responsive">
                <table class="table table-bordered whitespace-nowrap min-w-full">
                    <thead>
                        <tr class="!border-defaultborder dark:!border-defaultborder/10">
                            <th class="border-b dark:border-defaultborder/10 text-start">ID</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Nome</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Cognome</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Telefono</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Email</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Marketing</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Telegram</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Tenant</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Registration Source</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Created At</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Last Action</th>
                        


                        
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    
                        <tr class="!border-defaultborder dark:!border-defaultborder/10">
                            
                            <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $customer->id }}</td>
                            <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $customer->firstname }}</td>
                            <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $customer->lastname }}</td>
                            <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $customer->phone }}</td>
                            <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $customer->email }}</td>
                            <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                @if ($customer->consent_marketing)
                                        <span class="badge bg-success/10 !text-success">SI</span>
                                @else
                                    <span class="badge bg-danger/10 !text-danger">NO</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                    @if ($customer->telegramid)
                                        <span class="badge bg-success/10 !text-success">SI</span>
                                    @else
                                        <span class="badge bg-danger/10 !text-danger">NO</span>
                                    @endif
                                </td>
                            <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">@if ($tenant) {{ $tenant->name }} @else - @endif</td>
                            <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $customer->registration_source }}</td>
                            <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $customer->created_at?->format('d/m/Y H:i:s') ?? '—' }}</td>
                            <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $customer->last_action_at?->format('d/m/Y H:i:s') ?? '—' }}</td>
                            
                        </tr>
                            
                    </tbody>
                </table>
            </div>

            <div class="my-5"></div>

            <table class="table table-bordered whitespace-nowrap min-w-full">
                <thead>
                    <tr class="!border-defaultborder dark:!border-defaultborder/10">
                        <th class="border-b dark:border-defaultborder/10 text-start">Fidelity Accounts</th>


                      
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                  
                    <tr class="!border-defaultborder dark:!border-defaultborder/10">
               
                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                            @if($customer->fidelityAccounts->isEmpty())
                                <div class="p-6 bg-gray-50 rounded-md text-gray-700">
                                  {{$customer->display_name}} non risulta iscritto a nessuna fidelity card al momento.
                                </div>
                            @else
                                <div class="grid grid-cols-12 gap-6">
                                    @foreach($customer->fidelityAccounts as $acc)
                                        

                                        <div
                                        class="col-span-12 md:col-span-6 block border border-gray-200 bg-white rounded-md p-5 hover:shadow-sm">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <div class="font-semibold text-lg">{{ $acc->tenant?->name ?? 'Locale' }}</div>
                                                    <div class="text-sm text-gray-600">
                                                        Iscritto: {{ $acc->subscribed_at ? $acc->subscribed_at->format('d/m/Y') : '—' }}
                                                    </div>
                                                    <div class="text-xs text-gray-600 mt-1">
                                                        Totale accumulati: <span class="font-semibold">{{ (int) $acc->points_earned_total }}</span>
                                                        · Totale spesi: <span class="font-semibold">{{ (int) $acc->points_spent_total }}</span>
                                                    </div>
                                                </div>

                                                <div class="text-right">
                                                    <div class="text-2xl font-bold">{{ (int) $acc->points_balance }}</div>
                                                    <div class="text-sm text-gray-600">punti</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                             @endif
                        </td>
                    </tr>
                        
                </tbody>
            </table>

            <div class="my-5"></div>

                     <table class="table table-bordered whitespace-nowrap min-w-full">
                <thead>
                    <tr class="!border-defaultborder dark:!border-defaultborder/10">
                        <th class="border-b dark:border-defaultborder/10 text-start">Coupons</th>


                      
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                  
                    <tr class="!border-defaultborder dark:!border-defaultborder/10">
               
                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                            <?php
                            $coupons = \App\Models\CustomerCoupon::query()
                                ->where('customer_id', $this->customer->id)
                                ->with([
                                    'tenant:id,name,system_name',
                                    'tenantCoupon:id,tenant_id,tenant_coupon_id,slug,title_it,title_en,expires_at,is_active',
                                ])
                                ->orderByRaw("CASE WHEN validated_at IS NULL THEN 0 ELSE 1 END") // da usare prima
                                ->orderByDesc('redeemed_at')
                                ->paginate(20)
                                ->withQueryString();
                            ?>

                            @if($coupons->isEmpty())
                                <div class="p-6 bg-gray-50 rounded-md text-gray-700">
                                    {{$this->customer->display_name}} non ha ancora riscattato nessun coupon.
                                </div>
                            @else
                                <div class="grid grid-cols-12 gap-6">
                                    @foreach($coupons as $cc)
                                        @php
                                            $title = $cc->tenantCoupon?->title_it ?? 'Coupon';
                                            $tenantName = $cc->tenant?->name ?? 'Locale';
                                            $isUsed = !is_null($cc->validated_at) || $cc->status === 'validated';
                                            $expiresAt = $cc->tenantCoupon?->expires_at;
                                            $isExpired = $expiresAt && $expiresAt->isPast();
                                        @endphp

                                        <div 
                                        class="col-span-12 md:col-span-6 block border border-gray-200 bg-white rounded-md p-5 hover:shadow-sm">

                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <div class="font-semibold text-lg">{{ $title }}</div>
                                                    <div class="text-sm text-gray-600">{{ $tenantName }}</div>

                                                    <div class="text-xs text-gray-600 mt-2">
                                                        Riscattato: <span class="font-semibold">{{ $cc->redeemed_at ? $cc->redeemed_at->format('d/m/Y') : '—' }}</span>
                                                        @if($expiresAt)
                                                            · Scadenza: <span class="font-semibold">{{ $expiresAt->format('d/m/Y') }}</span>
                                                        @endif
                                                    </div>

                                                    <div class="text-xs text-gray-600 mt-1">
                                                        Codice: <span class="font-semibold">{{ $cc->code }}</span>
                                                    </div>
                                                </div>

                                                <div class="text-right">
                                                    @if($isUsed)
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                            Usato
                                                        </span>
                                                        <div class="text-xs text-gray-600 mt-2">
                                                            {{ $cc->validated_at ? $cc->validated_at->format('d/m/Y H:i') : '' }}
                                                        </div>
                                                    @else
                                                        @if($isExpired)
                                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                                                Scaduto
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                                Da usare
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-6">
                                    {{ $coupons->links() }}
                                </div>
                            @endif
                        </td>
                    </tr>
                        
                </tbody>
            </table>

            


        </div>

  
    </div>


    <div class="box">
        <div class="box-header border-none">
            <div class="flex justify-between">
                <div>
                    {{-- <div class="box-title pb-0">PIANTA: {{$name}}</div> --}}
                    {{-- <p class="text-xs text-gray-500 font-normal">Gestisci i tuoi prodotti qui.</p> --}}
                </div>
            </div>
        </div> 
        <div class="box-body">
            
        </div>
    </div> 



  @endif
</div>