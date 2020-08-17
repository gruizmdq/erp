<?php

namespace App;
use App\OrderSucursal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{   
    use SoftDeletes;
    protected $guarded = [];
    protected $primaryKey = 'order_id';
    
    public function orderable() {
        return $this->morphTo(__FUNCTION__, 'order_type', 'order_id');
    }

    public function order_items() {
        return $this->hasMany('App\OrderItem', 'id_order');
    }

    public function payments() {
        return $this->hasMany('App\OrderPayment', 'id_order');
    }
}
