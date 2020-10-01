<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Sucursal;
use App\User;
use Log;

class OrderSucursal extends Model
{   
    use SoftDeletes;
    protected $guarded = [];

    public function order() {
        return $this->morphOne('App\Order', 'order');
    }

    public function getDescription() {
        $description = new \stdClass();
        $description->sucursal = Sucursal::where('id', $this->id_sucursal)->value('name');
        $description->seller = User::where('id', $this->id_seller)->value('name');
        $description->cashier = User::where('id', $this->id_cashier)->value('name');
        return $description;
    }
}
