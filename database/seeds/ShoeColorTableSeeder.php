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
            "Blanco",
            "Verde",
            "Rojo",
            "Negro",
            "Beige",
            "Gris",
            "Azul",
            "Fuxcia",
            "Jean",
            "Chocolate",
            "Aero",
            "Neg/bco",
            "Marron",
            "Rosa",
            "Celeste",
            "Nude",
            "Plata",
            "Violeta",
            "Turquesa",
            "Naranja",
            "Navy",
            "Silver",
            "Black",
            "White",
            "Fosforesce",
            "Gris/rosa",
            "Brasil",
            "Neg/jean",
            "Oro",
            "Dorado",
            "Alpine",
            "Cinza",
            "Fucsia",
            "Coral",
            "Dark",
            "Brown",
            "Preto",
            "Marino",
            "Chumbo",
            "Bco/fux",
            "Rojo/azul",
            "Leopardo",
            "Beige/corazon",
            "Gris/corazon",
            "Fuxcia/print",
            "Lila",
            "Bordo",
            "Neg/gris",
            "Ani/fux",
            "Animal",
            "Ani/beige",
            "Neg/luna",
            "Bco/rosa",
            "Azul claro",
            "Bco/lila",
            "Bco/rojo",
            "Jean/rojo",
            "Cuadr/neg",
            "Cemento",
            "Rosa/gris",
            "Azu/gris",
            "Hielo",
            "Marfil",
            "Cuadros",
            "Suela",
            "Print",
            "Calavera",
            "Flor",
            "Oliva",
            "Uva",
            "Grabado",
            "Multicolor",
            "Crema",
            "Francia",
            "Oscuro",
            "Escoces",
            "Mickey",
            "Barcos",
            "Grey",
            "Blue",
            "Manzana",
            "Roca",
            "Lunares",
            "Coco",
            "Croco",
            "Leon",
            "Oxido",
            "Grafito",
            "Bronce",
            "Cuba",
            "Flores",
            "Frutilla",
            "Crudo",
            "Cuadrille",
            "Platino",
            "Tiza",
            "Esmeralda",
            "Camuflado",
            "Aqua",
            "Amarillo",
            "Selva",
            "Hindu",
            "Maiz",
            "Habano",
            "Charol",
            "Cebra",
            "Vison",
            "Monster",
            "Emoticon",
            "Topo",
            "Perla",
            "Liberti",
            "Arabe",
            "Palmera",
            "Tulipan",
            "Tropical",
            "Perro",
            "Dior",
            "Hojas",
            "Brillo",
            "Plumas",
            "Taupe",
            "Canela",
            "Envejecido",
            "Roble",
            "Gato",
            "Fluo",
            "Estrella",
            "Camel",
            "Peppa",
            "Avengers",
            "Maui",
            "Rayas",
            "Lirio",
            "Aleli",
            "Verde agua",
            "Unicornio",
            "Metalico",
            "Hollywood",
            "Arena",
            "Indigo",
            "Espiga",
            "Tachas",
            "Gamuza",
            "Cuero",
            "Crepe",
            "Napa",
            "Araña",
            "Gliter",
            "Cafe",
            "Champagne",
            "Vibora",
            "Agua",
            "Rouge",
            "Reptil",
            "Mostaza",
            "Sandia",
            "Bco/neg",
            "Unico",
            "Natural",
            "Bco/azu",
            "Azu/rosa",
            "Tornasol",
            "Petroleo",
            "Mate",
            "Iris",
            "Caramelo",
        ];
        foreach($colors as $color) {
            $new = new ShoeColor();
            $new->name = $color;
            $new->save();
        }
    }
}
