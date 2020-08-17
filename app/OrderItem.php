<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function order() {
        return $this->belongsTo('App\Order', 'foreign_key', 'id_order');
    }
}
