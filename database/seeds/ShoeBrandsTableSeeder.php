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
            "Jaguar",
            "Rave",
            "Oxigeno",
            "Diana",
            "Pira",
            "Sea walk",
            "Soft",
            "Crislor",
            "Agus",
            "Ipanema",
            "Havaiana",
            "Movida kids",
            "Modare",
            "Scarpino",
            "Footy",
            "Citadina",
            "Skaylap",
            "Hawaiian",
            "Stone",
            "Kidy",
            "Lualma",
            "Keek",
            "Comoditas",
            "Viamarte",
            "Vaner",
            "Korek",
            "Suffle",
            "Marcel",
            "Neo",
            "Angies",
            "Pasito",
            "Dreamer",
            "Dra.vidal",
            "Flywing",
            "Pegada",
            "Junior",
            "Gummi",
            "Hey day",
            "Savage",
            "Le utopik",
            "Jamaiquinas",
            "Native",
            "Goosy",
            "Lady stork",
            "Varent",
            "Guapitas",
            "Shadow",
            "Winpie",
            "Kyrios",
            "Creka",
            "Store",
            "Vizzano",
            "Massimo chiesa",
            "Beira rio",
            "Ale",
            "Gowell",
            "Hard top",
            "Mistral",
            "Lualmi",
            "Molekinho",
            "Key west",
            "Molekinha",
            "Moleca",
            "New tilers",
            "Piavitelli",
            "Rinar",
            "Selene",
            "Chiczone",
            "Caribeña",
            "Namoro",
            "Syndicate",
            "Carpincho",
        ];
        foreach($brands as $brand) {
            $new = new ShoeBrand();
            $new->name = $brand;
            $new->save();
        }
    }
}
