<?php

namespace App\Http\Controllers;

use Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TiendaNubeController extends Controller
{
    const LOG_LABEL = "[TIENDANUBE API]";
    const TIENDA_NUBE_API_URL = "https://api.tiendanube.com/v1/1153537/";

    public function order_created(Request $request) {
        Log::info(self::LOG_LABEL. " New order created: ". $request->getContent());

        Log::info(self::LOG_LABEL. " Request order data to tienda nube.");
        $this->sendRequest("orders/306030970");
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

    public function sendRequest($url) {
        $response = Http::withToken(\Config('tiendaNube.api_key'))
                    ->withHeaders([
                        'X-User-Agent:' => 'StartCalzados (name@email.com)',
                    ])
                    ->retry(3, 100);
        Log::info(self::TIENDA_NUBE_API_URL.$url);
        $response->get(self::TIENDA_NUBE_API_URL.$url);
        Log::info($response);

    }
}
