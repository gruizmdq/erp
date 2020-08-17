<?php

use Illuminate\Database\Seeder;
use App\OrderPaymentMethodCard;

class OrderPaymentMethodCardsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $new = new OrderPaymentMethodCard();
        $new->name = "Visa Crédito";
        $new->save();

        $new = new OrderPaymentMethodCard();
        $new->name = "Visa Débito";
        $new->save();
    }
}
