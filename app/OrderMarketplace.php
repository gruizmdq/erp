<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderMarketplace extends Model
{
    use SoftDeletes;

    public function order() {
        return $this->morphOne('App\Order', 'order');
    }
}
