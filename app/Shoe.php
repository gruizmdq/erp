<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\ShoeDetail;
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

    public function getItems() {
        return ShoeDetail::select(['shoe_details.number', 'shoe_details.buy_price', 'shoe_details.sell_price', 'shoe_details.available_tiendanube', 'shoe_details.available_marketplace'])
                            ->groupBy(['shoe_details.number', 'shoe_details.buy_price', 'shoe_details.sell_price', 'shoe_details.available_tiendanube', 'shoe_details.available_marketplace'])
                            ->where('shoe_details.id_shoe', $this->id)
                            ->get();
    }

    public function getColors() {
        return ShoeDetail::select("shoe_colors.*")
                ->join('shoe_colors', 'shoe_details.id_color', 'shoe_colors.id')
                ->where('shoe_details.id_shoe', $this->id)
                ->distinct('name')
                ->get();
    }

    public function getNumbers($color) {
        return ShoeDetail::select("number", "stock", "sell_price")
                ->where('id_shoe', $this->id)
                ->where('id_color', $color)
                ->get();
    }
}
