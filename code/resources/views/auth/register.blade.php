@extends('layouts.auth')
@section('content')
<div class="container">
    <div class="flex justify-center authentication authentication-basic items-center h-full text-defaultsize text-defaulttextcolor">
        <div class="box">
            <div class="box-body !p-[3rem]">
                <p class="h5 font-semibold mb-2 text-center !text-defaulttextcolor dark:!text-defaulttextcolor/85">Registrazione fornitore</p>
                <p class="mb-4 text-[#8c9097] opacity-[0.7] font-normal text-center">Iscriviti al sistema come fornitore e ricevi gli ordini dei clienti.</p>
                @session('status')
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ $value }}
                    </div>
                @endsession
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div>
                        <div class="grid xxl:grid-cols-2 xl:grid-cols-2 lg:grid-cols-2 md:grid-cols-2 sm:grid-cols-1 gap-5 mb-5">
                            <div class="w-full">
                                <label for="name" class="form-label text-default">Nome Azienda</label>
                                <input type="text" name="name" :value="old('name')" required autofocus autocomplete="name" class="form-control form-control-lg w-full !rounded-md" id="name" placeholder="Nome Azienda">
                            </div>
                            <div class="w-full">
                                <label for="piva" class="form-label text-default">P.iva</label>
                                <input type="text" name="piva" :value="old('piva')" required autofocus autocomplete="piva" class="form-control form-control-lg w-full !rounded-md" id="piva" placeholder="P.iva">
                            </div>
                        </div>

                        <div class="grid xxl:grid-cols-2 xl:grid-cols-2 lg:grid-cols-2 md:grid-cols-2 sm:grid-cols-1 gap-5 mb-5">
                            <div class="w-full">
                                <label for="email" class="form-label text-default">Email</label>
                                <input type="email" name="email" :value="old('email')" required autofocus autocomplete="email" class="form-control form-control-lg w-full !rounded-md" id="email" placeholder="e-mail">
                            </div>
                            <div class="w-full">
                                <label for="phone" class="form-label text-default">Telefono</label>
                                <input type="tel" name="phone" :value="old('phone')" required autofocus autocomplete="tel" class="form-control form-control-lg w-full !rounded-md" id="phone" placeholder="Telefono">
                            </div>
                        </div>

                        <div class="grid xxl:grid-cols-2 xl:grid-cols-2 lg:grid-cols-2 md:grid-cols-2 sm:grid-cols-1 gap-5 mb-5">
                            <div class="w-full">
                                <label for="password" class="form-label text-default block">Password</label>
                                <div class="input-group">
                                    <input name="password" required autocomplete="current-password" type="password" class="form-control form-control-lg !rounded-tl-md !rounded-bl-md" id="password" placeholder="password">
                                    <button class="ti-btn ti-btn-light !rounded-tl-none !rounded-bl-none !mb-0" type="button" onclick="createpassword('password',this)" id="button-addon2"><i class="ri-eye-off-line align-middle"></i></button>
                                </div>
                            </div>
                            <div class="w-full">
                                <label for="password_confirmation" class="form-label text-default block">Ripeti Password</label>
                                <div class="input-group">
                                    <input name="password_confirmation" required autocomplete="current-password" type="password" class="form-control form-control-lg !rounded-tl-md !rounded-bl-md" id="password_confirmation" placeholder="ripeti password">
                                    <button class="ti-btn ti-btn-light !rounded-tl-none !rounded-bl-none !mb-0" type="button" onclick="createpassword('password_confirmation',this)" id="button-addon3"><i class="ri-eye-off-line align-middle"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="grid xxl:grid-cols-3 xl:grid-cols-3 lg:grid-cols-3 md:grid-cols-3 sm:grid-cols-1 gap-5 mb-5">
                            <div class="w-full">
                                <label for="region_id" class="form-label text-default block">Regione</label>
                                <div class="input-group">
                                    <select name="region_id" required class="form-control form-control-lg !rounded-tl-md !rounded-bl-md" id="region_id">
                                        <option value="">Seleziona una Regione</option>
                                    </select>
                                </div>
                            </div>
                            <div class="w-full">
                                <label for="province_id" class="form-label text-default block">Provincia</label>
                                <div class="input-group">
                                    <select name="province_id" required class="form-control form-control-lg !rounded-tl-md !rounded-bl-md" id="province_id">
                                        <option value="">Seleziona una Provincia</option>
                                    </select>
                                </div>
                            </div>
                            <div class="w-full">
                                <label for="comuni_id" class="form-label text-default block">Comune</label>
                                <div class="input-group">
                                    <select name="comuni_id" required class="form-control form-control-lg !rounded-tl-md !rounded-bl-md" id="comuni_id">
                                        <option value="">Seleziona un Comune</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                        <div class="mb-5">
                            <div class="mt-4">
                                <label for="terms">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="terms" id="terms" required />
                                        <div class="ms-2">
                                            {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                                    'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Terms of Service').'</a>',
                                                    'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Privacy Policy').'</a>',
                                            ]) !!}
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        @endif

                        <div class="xl:col-span-12 col-span-12 grid mt-10">
                            <button type="submit" class="ti-btn ti-btn-lg bg-primary !border-0 text-white !font-medium">Iscriviti</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Script JS puro per la gestione dinamica delle select -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const regionSelect = document.getElementById('region_id');
    const provinceSelect = document.getElementById('province_id');
    const comuniSelect = document.getElementById('comuni_id');

    // Funzione per popolare una select data una lista di oggetti {id, name} e un elemento select
    function populateSelect(selectElement, items, placeholder) {
        selectElement.innerHTML = '';
        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = placeholder;
        selectElement.appendChild(placeholderOption);

        items.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.name;
            selectElement.appendChild(opt);
        });
    }

    // Carico le regioni all'avvio
    fetch('{{ route('location.regions') }}')
        .then(response => response.json())
        .then(data => {
            populateSelect(regionSelect, data, 'Seleziona una Regione');
        })
        .catch(error => console.error('Errore nel caricamento delle regioni:', error));

    // Quando cambia la regione
    regionSelect.addEventListener('change', function() {
        const regionId = regionSelect.value;
        // Svuoto province e comuni
        populateSelect(provinceSelect, [], 'Seleziona una Provincia');
        populateSelect(comuniSelect, [], 'Seleziona un Comune');

        if (regionId) {
            fetch('{{ url('/location/provinces') }}/' + regionId)
                .then(response => response.json())
                .then(data => {
                    populateSelect(provinceSelect, data, 'Seleziona una Provincia');
                })
                .catch(error => console.error('Errore nel caricamento delle province:', error));
        }
    });

    // Quando cambia la provincia
    provinceSelect.addEventListener('change', function() {
        const provinceId = provinceSelect.value;
        // Svuoto i comuni
        populateSelect(comuniSelect, [], 'Seleziona un Comune');

        if (provinceId) {
            fetch('{{ url('/location/comuni') }}/' + provinceId)
                .then(response => response.json())
                .then(data => {
                    populateSelect(comuniSelect, data, 'Seleziona un Comune');
                })
                .catch(error => console.error('Errore nel caricamento dei comuni:', error));
        }
    });

    // Funzione per toggle password
    window.createpassword = function(field, button) {
        const input = document.getElementById(field);
        if (input.type === 'password') {
            input.type = 'text';
            button.innerHTML = '<i class="ri-eye-line align-middle"></i>';
        } else {
            input.type = 'password';
            button.innerHTML = '<i class="ri-eye-off-line align-middle"></i>';
        }
    }
});
</script>
@endsection
