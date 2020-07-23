<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\OrderPaymentMethod;

class OrderController extends Controller
{
    public function get_payment_methods(Request $request) {
        return OrderPaymentMethod::get();
    }
}
