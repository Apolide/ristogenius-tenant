

    <div class="content">
        <!-- Start::main-content -->
        <div class="main-content">
            <!-- Page Header -->
            <!-- Page Header -->
            <div class="md:flex block items-center justify-between mb-6 mt-[2rem]  page-header-breadcrumb">
              <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">Customers</h5>
                <nav>
                  <ol class="flex items-center whitespace-nowrap min-w-0">
                    <li class="text-[12px]"> <a class="flex items-center text-primary hover:text-primary"
                        href="/"> Home <i
                          class="ti ti-chevrons-right flex-shrink-0 mx-3 overflow-visible text-textmuted rtl:rotate-180"></i>
                      </a> </li>
                    <li class="text-[12px]"> <a class="flex items-center text-textmuted"
                        href="javascript:void(0);">Customers
                      </a> </li>
                  </ol>
                </nav>
              </div>
    
              <div class="flex xl:my-auto right-content align-items-center">
                <div class="pe-1 xl:mb-0">
                  <button wire:click="$dispatch('click-create-customer')" type="button" class="ti-btn ti-btn-info-full text-white ti-btn-icon me-2 btn-b !mb-0">
                    <i class="mdi mdi-plus"></i>
                  </button>
                </div>
               
                <div class="pe-1 xl:mb-0">
                  <a href="/manage/customers" class="ti-btn ti-btn-warning-full text-white  ti-btn-icon me-2 !mb-0">
                    <i class="mdi mdi-refresh"></i>
                  </a>
                </div>
    
              </div>
            </div>
            <!-- Page Header Close -->
            <!-- Page Header Close -->
     
            
            

    

    @if($isVisible)
    <div class="box">
        <div class="box-header border-none">
            {{-- <div class="flex justify-between"> --}}
            <div>
                <div>
                    <div class="box-title pb-0">ELENCO CLIENTI</div>
                    <p class="text-xs text-gray-500 font-normal"></p>
                </div>
                <div class="mt-3 max-w-xs">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cerca cliente" class="form-control">
                </div>

                <div class="mt-3">

                    {{ $customers->links('vendor.livewire.tailwind') }}
                </div>
                <!-- Campo di ricerca -->
       
            </div>
        </div>

      
        
        
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered whitespace-nowrap min-w-full">
                    <thead>
                        <tr class="!border-defaultborder dark:!border-defaultborder/10">
                            <th class="border border-defaultborder dark:border-defaultborder/10 text-start">Azioni</th>
                           
                            <th class="border-b dark:border-defaultborder/10 text-start">Nome</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Cognome</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Telefono</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Email</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Marketing</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Telegram</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Last action</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Created At</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Tenant</th>
                            <th class="border-b dark:border-defaultborder/10 text-start">Registration Source</th>
  
                          
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                      
                        @foreach ($customers as $customer)
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <td class="whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        
                                        <div class="hs-tooltip ti-main-tooltip">
                                            <button wire:click="$dispatch('click-show-customer', { id: '{{ $customer->id }}' })" class="ti-btn ti-btn-sm hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm bg-info text-white hover:bg-info">
                                                <i class="las la-eye"></i>
                                                <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">
                                                    Vedi
                                                </span>
                                            </button>
                                        </div>
                                        <?php
                                        /*
                                        {{-- <div class="hs-tooltip ti-main-tooltip">
                                            <a href="/customers" class="ti-btn ti-btn-sm hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm bg-info text-white hover:bg-info">
                                                <i class="las la-eye"></i>
                                                <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">
                                                    Vedi
                                                </span>
                                            </a>
                                        </div> --}}
                                        */
                                        ?>
                                        {{-- <div class="hs-tooltip ti-main-tooltip">
                                            <button wire:click="$dispatch('click-edit-customer', { id: '{{ $customer->id }}' })" class="ti-btn ti-btn-sm hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm bg-warning text-white hover:bg-warning">
                                                <i class="las la-pen"></i>
                                                <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">
                                                    Modifica
                                                </span>
                                            </button>
                                        </div> --}}
                                        <div class="hs-tooltip ti-main-tooltip">
                                            <button wire:click="$dispatch('click-delete-customer', { id: '{{ $customer->id }}' })" class="ti-btn ti-btn-sm hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm bg-danger text-white hover:bg-danger">
                                                <i class="las la-trash"></i>
                                                <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">
                                                    Elimina
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                               
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
      
                                        
                                <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $customer->last_action_at?->format('d/m/Y H:i:s') ?? '—' }}</td>
                                <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $customer->created_at?->format('d/m/Y H:i:s') ?? '—' }}</td>

                                <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">@if ($customer->tenant) {{ $customer->tenant->name }} @else - @endif</td>
                                <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $customer->registration_source }}</td>
                              


                                
   

                            </tr>
                            
                        @endforeach

                        @if($customers->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center text-gray-500">Nessuna customer trovato.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

  
    </div>

    @endif

    <!-- Componenti per Modifica ed Eliminazione -->

    
    <livewire:customers.customer-show />
    {{-- <livewire:customers.customer-edit /> --}}
    <livewire:customers.customer-delete />

</div>
</div>
