<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShoeDetail extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function shoeSucursalItem() {
        return $this->hasMany('App\ShoeSucursalItem', 'id_shoe_detail');
    }
}
