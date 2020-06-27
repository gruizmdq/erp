<?php

use Illuminate\Database\Seeder;
use App\Shoe;

class ShoeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        for ($i = 0; $i < 1000; $i++){
            $shoe = new Shoe();
            $shoe->id_brand = rand (7 , 106);
            $shoe->code = ''.$i;
            $shoe->id_category = 1;
            $shoe->save();
        }
    }
}
