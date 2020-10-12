<?php

namespace App\Http\Controllers;

use Log;
use Illuminate\Http\Request;

class TiendaNubeController extends Controller
{
    const LOG_LABEL = "[TIENDANUBE API]";

    public function order_created(Request $request) {
        Log::info(self::LOG_LABEL. " ". $request->getContent());
    }

    public function order_paid(Request $request) {
        Log::info(self::LOG_LABEL. " ". $request->getContent());
    }

    public function order_updated(Request $request) {
        Log::info(self::LOG_LABEL. " ". $request->getContent());
    }

    public function order_cancelled(Request $request) {
        Log::info(self::LOG_LABEL. " ". $request->getContent());
    }
}
