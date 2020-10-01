<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\StockMovementItem;

class StockMovement extends Model
{
    public function items() {
        return $this->hasMany('App\StockMovementItem', 'id_stock_movement');
    }

    public function getSucursalNames() {
        return StockMovement::select(
            's1.name as sucursal_from',
            's2.name as sucursal_to'
           )
           ->join('sucursals as s1','stock_movements.id_sucursal_from', 's1.id')
           ->join('sucursals as s2','stock_movements.id_sucursal_to', 's2.id')
           ->where('stock_movements.id', $this->id)
           ->firstOrFail();
    }

    public function getItems() {
        return StockMovementItem::select(
            'stock_movement_items.qty',
            'shoe_details.number',
            'shoe_colors.name as color',
            'shoe_brands.name as brand',
            'shoes.code as code')
            ->join('shoe_details', 'shoe_details.id', 'stock_movement_items.id_shoe_detail')
            ->join('shoe_colors', 'shoe_colors.id', 'shoe_details.id_color')
            ->join('shoes', 'shoes.id', 'shoe_details.id_shoe')
            ->join('shoe_brands', 'shoes.id_brand', 'shoe_brands.id')
            ->where('stock_movement_items.id_stock_movement', $this->id)
            ->get();
    }
}
