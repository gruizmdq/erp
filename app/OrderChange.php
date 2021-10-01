<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderChange extends Model
{
    public function order() {
        return $this->morphOne('App\Order', 'order');
    }
}
