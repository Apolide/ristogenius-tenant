<?php

namespace App\Http\Controllers;

use App\Models\DigitalMenuProduct;
use App\Models\FidelityProgram;
use App\Models\FidelityReward;
use App\Services\CustomerLanguageService;
use Illuminate\Http\Request;

class FidelityController extends Controller
{
    private const COPY_FIELDS = ['form', 'activation_subject', 'activation_body', 'balance_subject', 'balance_body'];

    public function index(CustomerLanguageService $languages)
    {
        return view('fidelity.manage', [
            'program' => FidelityProgram::findOrFail(1),
            'rewards' => FidelityReward::orderBy('points')->orderBy('id')->get(),
            'products' => DigitalMenuProduct::get()->sortBy(fn (DigitalMenuProduct $product) => $product->translatedName()),
            'copyFields' => self::COPY_FIELDS,
            'languages' => $languages->enabled(),
            'defaultLanguage' => $languages->default(),
        ]);
    }

    public function update(Request $request, CustomerLanguageService $languages)
    {
        $rules = [
            'active' => 'sometimes|boolean', 'starting_points' => 'required|integer|min:0|max:1000000',
            'rate' => 'required|regex:/^\d{1,4}(\.\d{1,2})?$/|numeric|min:0|max:1000',
            'channels' => 'sometimes|array', 'channels.*' => 'in:dine_in,pickup,delivery|distinct',
            'booking_url' => 'nullable|url:http,https|max:2048',
        ];
        foreach (array_keys($languages->enabled()) as $language) {
            $rules['copy.'.$language] = 'sometimes|array:'.implode(',', self::COPY_FIELDS);
            foreach (self::COPY_FIELDS as $field) {
                $rules['copy.'.$language.'.'.$field] = str_ends_with($field, '_subject') ? 'nullable|string|max:160|not_regex:/[\r\n]/' : 'nullable|string|max:4000';
            }
        }
        $data = $request->validate($rules);
        [$units, $fraction] = array_pad(explode('.', (string) $data['rate']), 2, '');
        $program = FidelityProgram::findOrFail(1);
        $program->update([
            'active' => $request->boolean('active'), 'starting_points' => $data['starting_points'],
            'rate' => (int) $units * 100 + (int) str_pad($fraction, 2, '0'),
            'channels' => array_values($data['channels'] ?? []),
            'booking_url' => $data['booking_url'] ?? null, 'copy' => array_replace_recursive($program->copy, $data['copy'] ?? []),
        ]);

        return back()->with('fidelity_saved', true);
    }

    public function reward(Request $request, ?FidelityReward $reward = null)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'points' => 'required|integer|min:1|max:1000000',
            'kind' => 'required|in:discount,product',
            'amount' => 'exclude_unless:kind,discount|required|regex:/^\d{1,6}(\.\d{1,2})?$/|numeric|min:0.01',
            'product_id' => 'exclude_unless:kind,product|required|uuid|exists:digital_menu_products,id',
        ]);
        $reward ??= new FidelityReward;
        $reward->fill([
            'name' => $data['name'], 'points' => $data['points'], 'product_id' => $data['product_id'] ?? null,
            'discount_cents' => isset($data['amount']) ? (int) round((float) $data['amount'] * 100) : null,
        ])->save();

        return back()->with('fidelity_saved', true);
    }

    public function deleteReward(FidelityReward $reward)
    {
        $reward->delete();

        return back()->with('fidelity_saved', true);
    }
}
