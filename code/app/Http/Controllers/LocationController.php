<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Province;
use App\Models\Comuni;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function getRegions()
    {
        // Ritorna tutte le regioni
        return response()->json(Region::orderBy('name')->get());
    }

    public function getProvincesByRegion($regionId)
    {
        // Ritorna le province filtrate per regione
        $provinces = Province::where('region_id', $regionId)->orderBy('name')->get();
        return response()->json($provinces);
    }

    public function getComuniByProvince($provinceId)
    {
        // Ritorna i comuni filtrati per provincia
        $comuni = Comuni::where('province_id', $provinceId)->orderBy('name')->get();
        return response()->json($comuni);
    }
}
