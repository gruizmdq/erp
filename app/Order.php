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

    public function get_description() {
        $items = OrderItem::select([
                'order_items.buy_price',
                'order_items.sell_price',
                'order_items.total',
                'order_items.qty',
                'shoe_details.number',
                'shoes.code',
                'shoe_colors.name as color',
                'shoe_brands.name as brand'
            ])
            ->join('shoe_details', 'order_items.id_shoe_detail', 'shoe_details.id')
            ->join('shoes', 'shoe_details.id_shoe', 'shoes.id')
            ->join('shoe_brands', 'shoes.id_brand', 'shoe_brands.id')
            ->join('shoe_colors', 'shoe_details.id_color', 'shoe_colors.id')
            ->get();
    }

    public static function get_marketplace_orders($paginate = 15, $user = false) {
        $query = Order::select(
                        'orders.qty',
                        'orders.total',
                        'users.name as seller',
                        'order_marketplaces.*')
                ->join('order_marketplaces', 'orders.order_id', 'order_marketplaces.id')
                ->join('users', 'order_marketplaces.id_seller', 'users.id')
                ->orderBy('order_marketplaces.id', 'desc');
        if ($user)
            $query->where('order_marketplaces.id_seller', $user);
        return $query->paginate($paginate);
    }

    public function get_items() {
        return OrderItem::select([
            'order_items.id',
            'order_items.buy_price',
            'order_items.sell_price',
            'order_items.total',
            'order_items.qty',
            'shoe_details.number',
            'shoes.code',
            'shoe_colors.name as color',
            'shoe_brands.name as brand'
        ])
        ->join('shoe_details', 'order_items.id_shoe_detail', 'shoe_details.id')
        ->join('shoes', 'shoe_details.id_shoe', 'shoes.id')
        ->join('shoe_brands', 'shoes.id_brand', 'shoe_brands.id')
        ->join('shoe_colors', 'shoe_details.id_color', 'shoe_colors.id')
        ->where('order_items.id_order', $this->order_id)
        ->get();
    }
}
