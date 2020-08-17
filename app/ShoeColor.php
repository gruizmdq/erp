<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShoeColor extends Model
{
    use SoftDeletes;

    protected static function booted() {
        parent::boot();

        //CASCADE DELETE
        static::deleting(function (ShoeColor $shoeColor) {
            $shoeDetails = $shoeColor->shoeDetail;
            foreach($shoeDetails as $detail)            
                $detail->delete();
        });
    }

    public function shoeDetail() {
        return $this->hasMany('App\ShoeDetail', 'id_color');
    }
}
