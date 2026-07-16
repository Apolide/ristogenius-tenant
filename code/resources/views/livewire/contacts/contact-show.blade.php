<div>
    @if($isVisible)
    <div class="box">
        <div class="box-header border-none">
            <div class="flex justify-between">
                <div>
                    <div class="box-title pb-0">DETTAGLIO {{$name}}</div>
                    <button wire:click="back()" type="button" class="ti-btn ti-btn-light mt-3">
                        Indietro
                    </button>
                    {{-- <p class="text-xs text-gray-500 font-normal">Gestisci i tuoi prodotti qui.</p> --}}
                </div>
                
 
                
            </div>
        </div> 
        <div class="box-body">


            <table class="table table-bordered whitespace-nowrap min-w-full">
                <thead>
                    <tr class="!border-defaultborder dark:!border-defaultborder/10">
                        <th class="border-b dark:border-defaultborder/10 text-start">ID</th>
                        <th class="border-b dark:border-defaultborder/10 text-start">Nome</th>
                        <th class="border-b dark:border-defaultborder/10 text-start">Nome di sistema</th>
                        <th class="border-b dark:border-defaultborder/10 text-start">Email</th>


                      
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                  
                    <tr class="!border-defaultborder dark:!border-defaultborder/10">
                        
                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $contact->id }}</td>
                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $contact->name }}</td>
                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $contact->system_name }}</td>
                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $contact->email }}</td>
                    </tr>
                        
                </tbody>
            </table>

        </div>

  
    </div>


    <div class="box">
        <div class="box-header border-none">
            <div class="flex justify-between">
                <div>
                    <div class="box-title pb-0">PIANTA: {{$name}}</div>
                    {{-- <p class="text-xs text-gray-500 font-normal">Gestisci i tuoi prodotti qui.</p> --}}
                </div>
            </div>
        </div> 
        <div class="box-body">
            <livewire:contacts.contact-layout-manager />
        </div>
    </div> 



  @endif
</div>