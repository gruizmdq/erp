<?php

use Illuminate\Database\Seeder;
use App\ShoeBrand;
class ShoeBrandsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 100; $i++) {
            $new = new ShoeBrand();
            $new->name = "Marca ".$i;
            $new->save();
        }
    }
}
