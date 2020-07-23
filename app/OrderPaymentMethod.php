<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderPaymentMethod extends Model
{   
    //charge is a percentage from total
    const TYPE_PERCENTAGE = 1;
    //charge is a  fixed value
    const TYPE_CHARGE = 2;
}
