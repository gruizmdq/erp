<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\OrderSucursal;

class CashRegisterTurn extends Model
{
    public function movements() {
        return $this->hasMany('App\CashRegisterMovement', 'id_turn');
    }

    public function orders() {
        return $this->hasMany('App\OrderSucursal', 'id_turn');
    }

    public function getPayments() {
        return OrderSucursal::join('order_payments', 'order_sucursals.id', '=', 'order_payments.id_order')
                                ->where('order_sucursals.id_turn', $this->id)
                                ->select('order_payments.id_payment_method', DB::raw('sum(order_payments.total) as total'))
                                ->groupBy('order_payments.id_payment_method')
                                ->get();
    }
}
