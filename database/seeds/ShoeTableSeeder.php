<?php

use Illuminate\Database\Seeder;
use App\Shoe;
use App\ShoeBrand;

class ShoeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        $shoes = [
            ['Angies', '1083'],
            ['Angies', '1090'],
            ['Angies', '1093'],
            ['Angies', '2083'],
            ['Angies', '2090'],
            ['Angies', '3211'],
            ['Angies', '6320'],
            ['Beira rio', '41941519'],
            ['Comoditas', '193'],
            ['Comoditas', '379'],
            ['Footy', 'Cars507'],
            ['Footy', 'Footy'],
            ['Footy', 'Ppx990'],
            ['Footy', 'Pwx545'],
            ['Footy', 'Pwx546'],
            ['Footy', 'Wow437'],
            ['Footy', 'Wow441'],
            ['Footy', 'Wow442'],
            ['Footy', 'Wow444'],
            ['Footy', 'Wow445'],
            ['Footy', 'Wow446'],
            ['Hey day', '162'],
            ['Hey day', '163'],
            ['Hey day', '164'],
            ['Jaguar', '911'],
            ['Le utopik', 'Truck'],
            ['Lualma', 'Star'],
            ['Modare', '7354101'],
            ['Modare', '7358100'],
            ['Modare', '7362101'],
            ['Molekinha', '2126511'],
            ['Molekinha', '2131507'],
            ['Molekinha', '2131510'],
            ['Molekinha', '2503321'],
            ['Molekinha', '25201411'],
            ['Molekinha', '2524325'],
            ['Molekinha', '2540100'],
            ['Molekinha', '2540101'],
            ['Molekinha', '2541100'],
            ['Molekinha', '2541101'],
            ['Molekinha', '2542103'],
            ['Molekinha', '2715100'],
            ['Molekinho', '2615102'],
            ['Molekinho', '2808202'],
            ['Molekinho', '2832104'],
            ['Molekinho', '2832107'],
            ['Molekinho', '2838201'],
            ['Popys', '1500'],
            ['Popys', '678'],
            ['Rave', '10'],
            ['Soft', '640'],
            ['Soft', '660'],
            ['Soft', 'Soft'],
            ['Torshoes', 'Deli'],
            ['Torshoes', 'Marruecos'],
            ['Torshoes', 'Parma'],
            ['Vaner', 'Rp35'],
            ['Vanner', '3190'],
            ['Vanner', '3240'],
            ['Vanner', 'Rp15'],
            ['Vanner', 'Rp25'],
            ['Vanner', 'Rp35'],
            ['Vizzano', '12141014'],
            ['Vizzano', '1354.104'],
            ['Vizzano', '1354100'],
            ['Vizzano', '1356104'],
            ['Vizzano', '1360204'],
            ['Vizzano', '1362103'],
            ['Vizzano', '1362202'],
            ['Vouster', 'Lyon'],
        ];

        $brands = ShoeBrand::select('name', 'id')->get();

        foreach($shoes as $shoe) {
            $i = 0;
            while($i < count($brands) && $shoe[0] != $brands[$i]->name) {
                $i++;
            }
            $old = Shoe::where(
                [
                    ['id_brand', $brands[$i]->id],
                    ['code', $shoe[1]]
                ]
                )->first();

            if (!$old) {
                $new = new Shoe();
                $new->id_brand = $brands[$i]->id;
                $new->code = $shoe[1];
                $new->save();
                echo "{$new->code} {$brands[$i]->name}\n";
            }
        }
    }
}
