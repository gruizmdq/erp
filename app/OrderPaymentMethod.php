<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\OrderPaymentMethodCard;
use Log;

class OrderPaymentMethod extends Model
{   
    const CASH_PAYMENT_TYPE = 1;
    const CARD_PAYMENT_TYPE = 2;
    const CREDIT_NOTE_PAYMENT_TYPE = 3;
    const ACCOUNT_PAYMENT_TYPE = 4;
    
    protected static function booted() {
        parent::boot();

        static::retrieved(function (OrderPaymentMethod $method) {
            $method->cards = $method->get_cards($method);
        });
    }

    public function get_cards(OrderPaymentMethod $method) {
        if ($method->name == "Tarjeta") {
            return OrderPaymentMethodCard::get();
        }
        return null;
    }
}
