<?php

use Illuminate\Database\Seeder;
use App\OrderPaymentMethodCardOption;

class OrderPaymentMethodCardOptionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $new = new OrderPaymentMethodCardOption();
        $new->id_card = 2;
        $new->installments = 1;
        $new->charge = 0;
        $new->save();

        $new = new OrderPaymentMethodCardOption();
        $new->id_card = 2;
        $new->installments = 3;
        $new->charge = 0;
        $new->save();

        $new = new OrderPaymentMethodCardOption();
        $new->id_card = 2;
        $new->installments = 6;
        $new->charge = 10;
        $new->save();

        $new = new OrderPaymentMethodCardOption();
        $new->id_card = 2;
        $new->installments = 9;
        $new->charge = 15;
        $new->save();

        $new = new OrderPaymentMethodCardOption();
        $new->id_card = 2;
        $new->installments = 12;
        $new->charge = 20;
        $new->save();
    }
}
