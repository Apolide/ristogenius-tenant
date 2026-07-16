<?php 

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;


class CompanyController extends Controller
{
    public function getfromvat(Request $request)
    {
        $data = $request->validate([
            'contact_id' => 'required|string',
            'piva' => 'required|string'
        ]);

        $company = Company::where('fiscal_code', $data['piva'])->first();

        return response()->json($company);
    }
}

