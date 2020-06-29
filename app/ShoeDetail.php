<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShoeDetail extends Model
{
    use SoftDeletes;
    protected $fillable = ['id_shoe', 'id_color', 'number'];

    public function shoeSucursalItem() {
        return $this->hasMany('App\ShoeSucursalItem', 'id_shoe_detail');
    }
}
