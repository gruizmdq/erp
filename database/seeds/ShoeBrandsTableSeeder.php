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

        $brands = [
            "Angies",
            "Beira rio",
            "Comoditas",
            "Footy",
            "Hey day",
            "Jaguar",
            "Le utopik",
            "Lualma",
            "Modare",
            "Molekinha",
            "Molekinho",
            "Popys",
            "Rave",
            "Soft",
            "Torshoes",
            "Vaner",
            "Vanner",
            "Vizzano",
            "Vouster",
        ];
        foreach($brands as $brand) {
            $old = ShoeBrand::where('name', $brand)->first();
            if (!$old){
                $new = new ShoeBrand();
                $new->name = $brand;
                $new->save();
                echo $brand."\n";
            }
        }
    }
}
