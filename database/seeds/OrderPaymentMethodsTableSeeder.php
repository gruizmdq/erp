<?php

use Illuminate\Database\Seeder;
use App\OrderPaymentMethod;

class OrderPaymentMethodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $new = new OrderPaymentMethod();
        $new->name = "Efectivo";
        $new->type = 1;
        $new->percentage = 0;
        $new->installments = 1;
        $new->save();

        $new = new OrderPaymentMethod();
        $new->name = "Efectivo";
        $new->type = 1;
        $new->percentage = 0;
        $new->installments = 1;
        $new->save();

    }
}
