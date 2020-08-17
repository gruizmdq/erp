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
        $new->save();

        $new = new OrderPaymentMethod();
        $new->name = "Tarjeta";
        $new->save();

        $new = new OrderPaymentMethod();
        $new->name = "Nota de Crédito";
        $new->save();

        $new = new OrderPaymentMethod();
        $new->name = "Cuenta Corriente";
        $new->save();
    }
}
