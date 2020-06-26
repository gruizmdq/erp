<?php

use Illuminate\Database\Seeder;
use App\ShoeCategory;

class ShoeCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $category = new ShoeCategory();
        $category->name = "Kids";
        $category->save();

        $category = new ShoeCategory();
        $category->name = "Mujer";
        $category->save();
    }
}
