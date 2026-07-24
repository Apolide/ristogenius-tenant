<?php

namespace App\Livewire\Marketing\Forms;

use App\Models\MarketingForm;
use App\Services\MarketingForms\FormPublicAssetService;
use App\Services\MarketingForms\FormStyleService;
use Livewire\Component;
use Livewire\WithFileUploads;

class FormStyleEdit extends Component
{
    use WithFileUploads;

    public MarketingForm $form;

    public array $style = [];

    public $backgroundImage;

    public function mount(FormStyleService $styles): void
    {
        $this->style = $styles->for($this->form);
    }

    public function save(FormStyleService $styles, FormPublicAssetService $assets): void
    {
        $colorRules = [];
        foreach (array_keys(config('marketing_form_style.colors')) as $key) {
            $colorRules["style.{$key}"] = ['required', 'regex:/^#[0-9a-fA-F]{6}$/'];
        }
        $image = config('marketing_form_style.image');
        $data = $this->validate($colorRules + [
            'style.background_overlay' => ['required', 'integer', 'between:0,100'],
            'style.background_position' => ['required', 'in:left top,center top,right top,left center,center center,right center,left bottom,center bottom,right bottom'],
            'backgroundImage' => [
                'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.$image['max_size_kb'],
                'dimensions:min_width='.$image['min_width'].',min_height='.$image['min_height'],
            ],
        ]);

        $allowed = array_keys($styles->defaults());
        $values = collect($data['style'])->only($allowed)->all();
        $update = ['style_settings' => $values];
        if ($this->backgroundImage) {
            $update['image_path'] = $assets->replaceBackground($this->form, $this->backgroundImage);
        }
        $this->form->update($update);
        $this->form->refresh();
        $this->backgroundImage = null;
        session()->flash('success', 'Stile del form aggiornato.');
    }

    public function resetStyle(FormStyleService $styles): void
    {
        $this->form->update(['style_settings' => null]);
        $this->style = $styles->defaults();
        session()->flash('success', 'Stile predefinito ripristinato.');
    }

    public function removeBackground(FormPublicAssetService $assets): void
    {
        $assets->deleteBackground($this->form);
        $this->form->update(['image_path' => null]);
        $this->form->refresh();
        session()->flash('success', 'Immagine di sfondo rimossa.');
    }

    public function render(FormStyleService $styles, FormPublicAssetService $assets)
    {
        return view('livewire.marketing.forms.style-edit', [
            'defaults' => $styles->defaults(),
            'imageRules' => config('marketing_form_style.image'),
            'backgroundUrl' => $assets->backgroundUrl($this->form),
        ])->title('Stile form');
    }
}
