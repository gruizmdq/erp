<?php

use Illuminate\Database\Seeder;
use App\ShoeDetail;
use App\ShoeSucursalItem;
class ShoeDetailTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i<5000; $i++) {
            try{
                $new = new ShoeDetail();
                $new->stock = rand (1 , 300);
                $new->id_shoe = rand (1 , 1000);
                $new->id_color = rand (1 , 10);
                $new->number = rand (15 , 50);
                $new->buy_price = rand (150, 5000);
                $new->sell_price = rand (150, 5000);
                $new->save();

                $sucursal_item = new ShoeSucursalItem();
                $sucursal_item->stock = $new->stock;
                $sucursal_item->id_shoe_detail = $new->id;
                $sucursal_item->id_sucursal = 1;
                $sucursal_item->save();
            }
            catch(Exception $e) {
                echo $e;
            }   
        }
    }
}
