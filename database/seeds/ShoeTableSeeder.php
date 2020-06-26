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
        $shoe = new Shoe();
        $shoe->id_brand = 1;
        $shoe->code = "3412";
        $shoe->id_category = 1;
        $shoe->save();

        $shoe = new Shoe();
        $shoe->id_brand = 1;
        $shoe->code = "3413";
        $shoe->id_category = 1;
        $shoe->save();

        $shoe = new Shoe();
        $shoe->id_brand = 1;
        $shoe->code = "3414";
        $shoe->id_category = 1;
        $shoe->save();
    }
}
