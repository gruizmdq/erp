<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderSucursal extends Model
{   
    use SoftDeletes;
    protected $guarded = [];

    public function order() {
        return $this->morphOne('App\Order', 'order');
    }
}
