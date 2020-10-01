<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StockMovementItem extends Model
{
    public function stockMovement() {
        return $this->belongsTo('App\StockMovement', 'foreign_key', 'id_stock_movement');
    }
}
