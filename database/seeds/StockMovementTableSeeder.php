<?php

use Illuminate\Database\Seeder;
use App\StockMovement;

class StockMovementTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i<50; $i++) {
            $new =  new StockMovement();
            $new->id_shoe_detail = rand(10, 5000);
            $new->qty = rand(1, 25);
            $new->id_sucursal_from = rand(1, 2);
            if ($new->id_sucursal_from = 1)
                $new->id_sucursal_to = 2;
            else
                $new->id_sucursal_to = 1;
            $new->save();
        }
    }
}
