<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Exceptions\Stock\ExistsSucursalItemException;

class ShoeDetail extends Model
{
    use SoftDeletes;
    protected $fillable = ['id_shoe', 'id_color', 'number'];

    protected static function booted() {
        parent::boot();

        //CASCADE DELETE
        static::deleting(function (ShoeDetail $shoeDetail) {
            $sucursal_items = $shoeDetail->shoeSucursalItem;
            foreach ($sucursal_items as $item) {
                //Check if there is stock. 
                if ($item->stock > 0)
                    throw new ExistsSucursalItemException;
                    
            }
            $sucursal_items->delete();
        });
    }

    public function shoeSucursalItem() {
        return $this->hasMany('App\ShoeSucursalItem', 'id_shoe_detail');
    }
}
