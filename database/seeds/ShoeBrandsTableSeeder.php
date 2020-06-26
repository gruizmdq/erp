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
        $brand = new ShoeBrand();
        $brand->name = 'Hey Dey';
        $brand->save();

        $brand = new ShoeBrand();
        $brand->name = 'Rave';
        $brand->save();
    }
}
