<?php

use Illuminate\Database\Seeder;
use App\ShoeColor;
class ShoeColorTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $color = new ShoeColor();
        $color->name = 'Azúl';
        $color->save();

        $color = new ShoeColor();
        $color->name = 'Negro';
        $color->save();

        $color = new ShoeColor();
        $color->name = 'Rojo';
        $color->save();
    }
}
