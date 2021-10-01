<?php

namespace App;

use App\OrderSucursal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Order extends Model
{   
    use SoftDeletes;
    protected $guarded = [];
    protected $primaryKey = 'order_id';
    private const PAGINATE_SIZE = 50;

    const TYPE_ALL = '0';
    const TYPE_MARKETPLACE = '1';
    const TYPE_SUCURSALES = '2';
    const TYPE_TIENDANUBE = '3';

    const SUCURSAL_ALL = '0';
    const SUCURSAL_START = '1';
    const SUCURSAL_RUFINA = '2';
    
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
        return $items = OrderItem::select([
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

    static function get_orders($filters, $type) {
        $where = [
            ['orders.created_at', '>=', $filters["dateFrom"]],
            ['orders.created_at', '>=', $filters["dateTo"]]
        ];

        $orders = DB::table('orders')->where($where);
        $order_buy_price = OrderItem::select('id_order', DB::raw('sum(buy_price) as buy_price'))
                            ->groupBy('id_order');
        $orders = $orders->joinSub($order_buy_price, 'buy_price', function($join) {
                        $join->on('orders.order_id', '=', 'buy_price.id_order');
                });
        
        //GET SUCURSAL NAME!
        if ($type == 0) {
            $sucursal = Sucursal::select('name as type', 'order_sucursals.id as id_order')
                                ->join('order_sucursals', 'sucursals.id', 'order_sucursals.id_sucursal');
            $orders->joinSub($sucursal, 'sucursal', function($join) {
                $join->on('orders.order_id', 'sucursal.id_order');
            });
        }

        //TODO obtener ordenes de tienda nube, marketplace, etc.
        return $orders->paginate(self::PAGINATE_SIZE);
    }
}
