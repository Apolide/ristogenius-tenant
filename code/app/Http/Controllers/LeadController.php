<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LeadController extends Controller
{


    public function leadslist(){
        return view('leads-static');
    }

}