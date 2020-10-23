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
            "Activita",
            "Agus",
            "Ale",
            "Angies",
            "Beira rio",
            "Caribeña",
            "Carpincho",
            "Chiczone",
            "Citadina",
            "Cocoa kids",
            "Comoditas",
            "Cordones",
            "Crislor",
            "Diana",
            "Disney",
            "Dox",
            "Dreamer",
            "Federica",
            "Flywing",
            "Footy",
            "Goosy",
            "Gowell",
            "Guapitas",
            "Gummi",
            "Hard top",
            "Havaiana",
            "Hey day",
            "Ipanema",
            "Jaguar",
            "Jamaiquinas",
            "Junior",
            "Keek",
            "Key west",
            "Korek",
            "Kyrios",
            "Lady stork",
            "Le utopik",
            "Lualma",
            "Lualmi",
            "Marcel",
            "Maskotas",
            "Massimo chiesa",
            "Mistral",
            "Modare",
            "Moleca",
            "Molekinha",
            "Molekinho",
            "Movida kids",
            "Namoro",
            "Native",
            "Neo",
            "New tilers",
            "Oxigeno",
            "Pampero",
            "Piavitelli",
            "Pira",
            "Rave",
            "Rinar",
            "Savage",
            "Sea walk",
            "Selene",
            "Shadow",
            "Soft",
            "Store",
            "Syndicate",
            "Vaner",
            "Varent",
            "Viamarte",
            "Vizzano",
            "Winpie",
            "Zapatino",
        ];
        foreach($brands as $brand) {
            $new = new ShoeBrand();
            $new->name = $brand;
            $new->save();
        }
    }
}
