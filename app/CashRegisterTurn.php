<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashRegisterTurn extends Model
{
    public function movements() {
        return $this->hasMany('App\CashRegisterMovement', 'id_turn');
    }

    public function orders() {
        return $this->hasMany('App\OrderSucursal', 'id_turn');
    }
}
