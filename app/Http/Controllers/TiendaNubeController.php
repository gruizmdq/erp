<?php

namespace App\Http\Controllers;

use Log;
use Illuminate\Http\Request;

class TiendaNubeController extends Controller
{
    
    public function index(Request $request) {
        Log::info(config('tiendaNube.api_key'));
        return 'hola';
    }
}
