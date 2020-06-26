<?php

use Illuminate\Database\Seeder;
use App\ShoeNumber;

class ShoeNumberTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 18; $i < 50; $i++){
            $number = new ShoeNumber();
            $number->number = $i;
            $number->save();
        }
    }
}
