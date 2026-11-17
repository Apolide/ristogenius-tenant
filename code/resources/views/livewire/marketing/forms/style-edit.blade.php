<div class="content">
    <div class="main-content">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h5 class="page-title">{{ __('Stile:') }} {{ $form->title() }}</h5>
            <div class="flex gap-2"><a href="{{ route('marketing.forms.edit', $form) }}" class="ti-btn ti-btn-light">{{ __('Modifica form') }}</a><a href="{{ route('marketing.forms.index') }}" class="ti-btn ti-btn-light">{{ __('Torna alla lista') }}</a></div>
        </div>
        @if(session('success'))<div class="alert alert-success mb-5">{{ session('success') }}</div>@endif

        <form wire:submit="save" class="box">
            <div class="box-header"><div class="box-title">{{ __('Colori del form pubblico') }}</div></div>
            <div class="box-body grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach(config('marketing_form_style.colors') as $key => $default)
                    <div>
                        <label for="style-{{ $key }}" class="mb-2 block font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}</label>
                        <div class="flex gap-2"><input id="style-{{ $key }}" type="color" wire:model.live="style.{{ $key }}" class="h-10 w-14 cursor-pointer rounded border border-defaultborder p-1"><input wire:model.live="style.{{ $key }}" class="form-control font-mono" maxlength="7"></div>
                        @error('style.'.$key)<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                @endforeach
                <div><label class="mb-2 block font-medium">{{ __('Oscuramento sfondo (%)') }}</label><input type="number" min="0" max="100" wire:model.live="style.background_overlay" class="form-control">@error('style.background_overlay')<span class="text-danger">{{ $message }}</span>@enderror</div>
                <div><label class="mb-2 block font-medium">{{ __('Posizione immagine') }}</label><select wire:model.live="style.background_position" class="form-control"><option value="center center">{{ __('Centro') }}</option><option value="center top">{{ __('Centro in alto') }}</option><option value="center bottom">{{ __('Centro in basso') }}</option><option value="left center">{{ __('Sinistra') }}</option><option value="right center">{{ __('Destra') }}</option></select></div>
            </div>

            <div class="box-header border-t border-defaultborder"><div class="box-title">{{ __('Anteprima') }}</div></div>
            <div class="box-body" style="background-color: {{ $style['page_background'] }}; color: {{ $style['text'] }};">
                <div class="mx-auto max-w-2xl rounded-xl p-5 shadow" style="background-color: {{ $style['form_background'] }}; border-radius: {{ $style['form_radius'] }};">
                    <h2 class="mb-4 text-xl font-semibold" style="color: {{ $style['heading'] }};">{{ $form->title() }}</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="mb-2 block" style="color: {{ $style['label'] }};">{{ __('Nome') }}</label><input class="form-control" value="Mario" readonly style="background-color: {{ $style['input_background'] }}; color: {{ $style['input_text'] }}; border-color: {{ $style['input_border'] }};"></div>
                        <div><label class="mb-2 block" style="color: {{ $style['label'] }};">Email</label><input class="form-control" value="mario@example.test" readonly style="background-color: {{ $style['input_background'] }}; color: {{ $style['input_text'] }}; border-color: {{ $style['input_border'] }};"></div>
                    </div>
                    <div class="mt-4 flex items-center justify-between"><a href="#" onclick="return false" style="color: {{ $style['link'] }}; text-decoration: underline;">{{ __('Link di esempio') }}</a><button type="button" class="rounded px-4 py-2" style="background-color: {{ $style['button_background'] }}; color: {{ $style['button_text'] }};">{{ __('Invia') }}</button></div>
                </div>
            </div>

            <div class="box-header border-t border-defaultborder"><div class="box-title">{{ __('Immagine di sfondo') }}</div></div>
            <div class="box-body">
                <p class="mb-3 text-sm text-textmuted">{{ __('JPG, PNG o WebP. Minimo') }} {{ $imageRules['min_width'] }}×{{ $imageRules['min_height'] }} {{ __('px; consigliato') }} {{ $imageRules['recommended_width'] }}×{{ $imageRules['recommended_height'] }} {{ __('px; massimo') }} {{ round($imageRules['max_size_kb'] / 1024, 1) }} {{ __('MB. Mantieni il soggetto principale al centro perché l’immagine verrà ritagliata con “cover”.') }}</p>
                @if($backgroundUrl)<img src="{{ $backgroundUrl }}" class="mb-3 h-48 w-full rounded object-cover" alt="Sfondo attuale">@endif
                <input type="file" wire:model="backgroundImage" accept="image/jpeg,image/png,image/webp" class="form-control" x-data x-on:change="const file=$event.target.files[0]; if(!file)return; const image=new Image(); image.onload=()=>{ if(image.width < {{ $imageRules['min_width'] }} || image.height < {{ $imageRules['min_height'] }}) { alert('Immagine troppo piccola: dimensione minima {{ $imageRules['min_width'] }}×{{ $imageRules['min_height'] }} px.'); $event.target.value=''; } URL.revokeObjectURL(image.src); }; image.src=URL.createObjectURL(file);">
                @error('backgroundImage')<span class="text-danger">{{ $message }}</span>@enderror
                @if($form->image_path)<button type="button" wire:click="removeBackground" wire:confirm="{{ __('Rimuovere l’immagine di sfondo?') }}" class="ti-btn ti-btn-danger mt-3">{{ __('Rimuovi sfondo') }}</button>@endif
            </div>
            <div class="box-footer flex flex-wrap gap-2"><button class="ti-btn ti-btn-success">{{ __('Salva stile') }}</button><button type="button" wire:click="resetStyle" wire:confirm="{{ __('Ripristinare i valori predefiniti del tenant?') }}" class="ti-btn ti-btn-light">{{ __('Ripristina predefiniti') }}</button></div>
        </form>
    </div>
</div>
