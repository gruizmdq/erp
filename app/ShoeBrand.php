<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Shoe;
use Log;

class ShoeBrand extends Model
{
    use SoftDeletes;

    protected static function booted() {
        parent::boot();

        //CASCADE DELETE
        static::deleting(function (ShoeBrand $brand) {
            $shoes = $brand->shoes;
            foreach($shoes as $shoe)
                $shoe->delete();
        });
    }

    public function shoes() {
        return $this->hasMany('App\Shoe', 'id_brand');
    }
}
