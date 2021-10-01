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

    public static function createItem($orderId, $item, $type = 'OUT') {
        $new = OrderItem::create([
            'id_order' => $orderId,
            'id_shoe_detail' => $item['id'],
            'qty' => $item['qty'],
            'buy_price' => $item['buy_price'],
            'sell_price' => $item['sell_price'],
            'total' => $item['price'],
            'type' => $type
        ]);
        return $new;
    }
}
