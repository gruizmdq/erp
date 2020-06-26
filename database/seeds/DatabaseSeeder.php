<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //$this->call(UserSeeder::class);
        //$this->call(ShoeBrandsTableSeeder::class);
        //$this->call(ShoeColorTableSeeder::class);
        //$this->call(ShoeNumberTableSeeder::class);
        //$this->call(ShoeCategoryTableSeeder::class);
        $this->call(ShoeTableSeeder::class);
    }
}
