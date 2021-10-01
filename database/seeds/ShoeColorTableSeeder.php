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
        $colors = [
            "Aero",
"Azul",
"Azul/neg",
"Azul/verde",
"Bco/neg",
"Bco/oro",
"Bco/rosa",
"Bco/tornasol",
"Beige",
"Blanco",
"Blanco/blanco",
"Bordo/crepe",
"Camuflado",
"Fucsia",
"Gris",
"Gris/neg",
"Gris/verde",
"Lila",
"Maiz",
"Marino",
"Marino/agua",
"Neg/bco",
"Neg/charol",
"Neg/crepe",
"Neg/croco",
"Neg/fucsia",
"Neg/marron",
"Neg/neg",
"Neg/oro",
"Neg/plata",
"Neg/tornasol",
"Neg/verde",
"Neg/violeta",
"Negro",
"Nude",
"Nude/oro",
"Rosa",
"Rosa/brillo",
"Rosa/gli",
"Suela",
"Tostado",
"Verde",
"Vison",
        ];
        foreach($colors as $color) {
            $old = ShoeColor::where('name', $color)->first();
            if (!$old) {
                $new = new ShoeColor();
                $new->name = $color;
                $new->save();
            }
        }
    }
}
