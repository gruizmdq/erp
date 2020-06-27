<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShoeSucursalItem extends Model
{
    use SoftDeletes;
    protected $fillable = ['id_sucursal', 'stock'];
    
    public function shoeDetail() {
        return $this->belongsTo('App\ShoeDetail', 'foreign_key', 'id_shoe_detail');
    }
}
