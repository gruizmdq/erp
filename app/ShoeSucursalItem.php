<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;

class ShoeSucursalItem extends Model
{
    use SoftDeletes;
    protected $fillable = ['id_sucursal', 'stock'];
    
    public function shoeDetail() {
        return $this->belongsTo('App\ShoeDetail', 'foreign_key', 'id_shoe_detail');
    }

    public static function updateItem($id, $idSucursal, $qty) {
        Log::info("Updating sucursal stock for item (id: {$id})");

        $sucursalItem = ShoeSucursalItem::where([
            ['id_shoe_detail', $id],
            ['id_sucursal', $idSucursal]
            ])->first();
        $sucursalItem->stock += $qty;
        $sucursalItem->save();


        $shoeDetail = ShoeDetail::where('id', $id)->first();
        $shoeDetail->stock += $qty;
        $shoeDetail->save();
        
        Log::info("Sucursal stock for item (id: {$id}) updated");

        return $sucursalItem;

    }
}
