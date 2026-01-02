<div>

    
    <div class="py-8 px-4 bg-panna rounded-lg">
        
        <form wire:submit.prevent="submit">


            <div class="w-full mb-5">
                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome</label>
                <input required wire:model="name" type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="nome">
            </div>

            <div class="w-full mb-5">
                <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">E-mail</label>
                <input required wire:model="email" type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e-mail">
            </div>

            <div class="w-full">
                <label for="role" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Ruolo</label>
                <select required wire:model="role" id="role" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    
                    @foreach ($role_list as $singleRole)
                        @if($role == $singleRole)
                            <option value="{{$singleRole}}" selected>{{$singleRole}}</option>
                        @else
                            <option value="{{$singleRole}}">{{$singleRole}}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            
            <button wire:target="submit" wire:loading.attr="disabled" type="submit" class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-panna bg-bordeaux rounded-lg hover:bg-red-800">
                Salva
            </button>
        </form>
    </div>

  
    
</div>
