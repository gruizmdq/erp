<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;
class Shoe extends Model
{
    use SoftDeletes;

    protected static function booted() {
        parent::boot();

        //CASCADE DELETE
        static::deleting(function (Shoe $shoe) {
            $shoeDetails = $shoe->shoeDetail;
            foreach($shoeDetails as $detail)            
                $detail->delete();
        });
    }

    public function shoeDetail() {
        return $this->hasMany('App\ShoeDetail', 'id_shoe');
    }
}
