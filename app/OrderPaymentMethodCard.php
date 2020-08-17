<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderPaymentMethodCard extends Model
{
    protected static function booted() {
        parent::boot();

        static::retrieved(function (OrderPaymentMethodCard $card) {
            $card->options;
        });
    }

    public function options() {
        return $this->hasMany('App\OrderPaymentMethodCardOption', 'id_card');
    }
}
