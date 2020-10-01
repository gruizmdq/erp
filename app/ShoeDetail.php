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
        
        //TODO: debuger que onda cuando esta softdeleteado el articulo y se quiere ingresar uno nuevo con existing keys
        
    }

    public function shoeSucursalItem() {
        return $this->hasMany('App\ShoeSucursalItem', 'id_shoe_detail');
    }

    public function getDescription() {
        $shoe =  ShoeDetail::select('shoe_brands.name as brand', 'shoe_colors.name as color', 'shoes.code as code')
                    ->join('shoes', 'shoes.id', 'shoe_details.id_shoe')
                    ->join('shoe_brands', 'shoes.id_brand', 'shoe_brands.id')
                    ->join('shoe_colors', 'shoe_details.id_color', 'shoe_colors.id')
                    ->where('shoe_details.id', $this->id)
                    ->first();
        return "{$shoe->brand} {$shoe->code} {$shoe->color} Nro $this->number";
    }
}
