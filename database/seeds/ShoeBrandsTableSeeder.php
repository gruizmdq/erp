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
            "Agus",
            "Ale",
            "Beira rio",
            "Carpincho",
            "Chiczone",
            "Citadina",
            "Comoditas",
            "Crislor",
            "Diana",
            "Flywing",
            "Footy",
            "Goosy",
            "Gowell",
            "Havaiana",
            "Hey day",
            "Ipanema",
            "Jaguar",
            "Jamaiquinas",
            "Junior",
            "Keek",
            "Korek",
            "Kyrios",
            "Lady stork",
            "Le utopik",
            "Lualma",
            "Lualmi",
            "Mistral",
            "Modare",
            "Molekinha",
            "Molekinho",
            "Movida kids",
            "Namoro",
            "Native",
            "Neo",
            "New tilers",
            "None",
            "Piavitelli",
            "Rave",
            "Rinar",
            "Savage",
            "Sea walk",
            "Shadow",
            "Soft",
            "Vaner",
            "Viamarte",
            "Vizzano",
            "Winpie",
        ];
        foreach($brands as $brand) {
            $new = new ShoeBrand();
            $new->name = $brand;
            $new->save();
        }
    }
}
