<div class="content">
    <div class="main-content">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h5 class="page-title">{{ __('marketing_forms.style') }} {{ $form->title() }}</h5>
            <div class="flex gap-2"><a href="{{ route('marketing.forms.edit', $form) }}" class="ti-btn ti-btn-light">{{ __('marketing_forms.edit') }}</a><a href="{{ route('marketing.forms.index') }}" class="ti-btn ti-btn-light"><i class="las la-arrow-left" aria-hidden="true"></i> {{ __('marketing_forms.back_to_list') }}</a></div>
        </div>
        @if(session('success'))<div class="alert alert-success mb-5">{{ session('success') }}</div>@endif

        <form wire:submit="save" class="box">
            <div class="box-header"><div class="box-title">{{ __('marketing_forms.public_form_colors') }}</div></div>
            <div class="box-body grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach(config('marketing_form_style.colors') as $key => $default)
                    <div>
                        <label for="style-{{ $key }}" class="mb-2 block font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}</label>
                        <div class="flex gap-2"><input id="style-{{ $key }}" type="color" wire:model.live="style.{{ $key }}" class="h-10 w-14 cursor-pointer rounded border border-defaultborder p-1"><input wire:model.live="style.{{ $key }}" class="form-control font-mono" maxlength="7"></div>
                        @error('style.'.$key)<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                @endforeach
                <div><label class="mb-2 block font-medium">{{ __('marketing_forms.background_darkening') }}</label><input type="number" min="0" max="100" wire:model.live="style.background_overlay" class="form-control">@error('style.background_overlay')<span class="text-danger">{{ $message }}</span>@enderror</div>
                <div><label class="mb-2 block font-medium">{{ __('marketing_forms.image_position') }}</label><select wire:model.live="style.background_position" class="form-control"><option value="center center">{{ __('marketing_forms.center') }}</option><option value="center top">{{ __('marketing_forms.top_center') }}</option><option value="center bottom">{{ __('marketing_forms.bottom_center') }}</option><option value="left center">{{ __('marketing_forms.left') }}</option><option value="right center">{{ __('marketing_forms.right') }}</option></select></div>
            </div>

            <div class="box-header border-t border-defaultborder"><div class="box-title">{{ __('marketing_forms.preview') }}</div></div>
            <div class="box-body" style="background-color: {{ $style['page_background'] }}; color: {{ $style['text'] }};">
                <div class="mx-auto max-w-2xl rounded-xl p-5 shadow" style="background-color: {{ $style['form_background'] }}; border-radius: {{ $style['form_radius'] }};">
                    <h2 class="mb-4 text-xl font-semibold" style="color: {{ $style['heading'] }};">{{ $form->title() }}</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="mb-2 block" style="color: {{ $style['label'] }};">{{ __('marketing_forms.name') }}</label><input class="form-control" value="Mario" readonly style="background-color: {{ $style['input_background'] }}; color: {{ $style['input_text'] }}; border-color: {{ $style['input_border'] }};"></div>
                        <div><label class="mb-2 block" style="color: {{ $style['label'] }};">Email</label><input class="form-control" value="mario@example.test" readonly style="background-color: {{ $style['input_background'] }}; color: {{ $style['input_text'] }}; border-color: {{ $style['input_border'] }};"></div>
                    </div>
                    <div class="mt-4 flex items-center justify-between"><a href="#" onclick="return false" style="color: {{ $style['link'] }}; text-decoration: underline;">{{ __('marketing_forms.example_link') }}</a><button type="button" class="rounded px-4 py-2" style="background-color: {{ $style['button_background'] }}; color: {{ $style['button_text'] }};">{{ __('marketing_forms.submit') }}</button></div>
                </div>
            </div>

            <div class="box-header border-t border-defaultborder"><div class="box-title">{{ __('marketing_forms.background_image') }}</div></div>
            <div class="box-body">
                <p class="mb-3 text-sm text-textmuted">{{ __('marketing_forms.image_formats_and_minimum') }} {{ $imageRules['min_width'] }}×{{ $imageRules['min_height'] }} {{ __('marketing_forms.pixels_recommended') }} {{ $imageRules['recommended_width'] }}×{{ $imageRules['recommended_height'] }} {{ __('marketing_forms.pixels_maximum') }} {{ round($imageRules['max_size_kb'] / 1024, 1) }} {{ __('marketing_forms.background_crop_help') }}</p>
                @if($backgroundUrl)<img src="{{ $backgroundUrl }}" class="mb-3 h-48 w-full rounded object-cover" alt="Sfondo attuale">@endif
                <input type="file" wire:model="backgroundImage" accept="image/jpeg,image/png,image/webp" class="form-control" x-data x-on:change="const file=$event.target.files[0]; if(!file)return; const image=new Image(); image.onload=()=>{ if(image.width < {{ $imageRules['min_width'] }} || image.height < {{ $imageRules['min_height'] }}) { alert('Immagine troppo piccola: dimensione minima {{ $imageRules['min_width'] }}×{{ $imageRules['min_height'] }} px.'); $event.target.value=''; } URL.revokeObjectURL(image.src); }; image.src=URL.createObjectURL(file);">
                @error('backgroundImage')<span class="text-danger">{{ $message }}</span>@enderror
                @if($form->image_path)<button type="button" wire:click="removeBackground" wire:confirm="{{ __('marketing_forms.remove_the_background_image') }}" class="ti-btn ti-btn-danger mt-3"><i class="las la-trash" aria-hidden="true"></i> {{ __('marketing_forms.remove_background') }}</button>@endif
            </div>
            <div class="box-footer flex flex-wrap gap-2"><button class="ti-btn ti-btn-success">{{ __('marketing_forms.save_style') }}</button><button type="button" wire:click="resetStyle" wire:confirm="{{ __('marketing_forms.restore_tenant_defaults') }}" class="ti-btn ti-btn-light">{{ __('marketing_forms.restore_defaults') }}</button></div>
        </form>
    </div>
</div>
