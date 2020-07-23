<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User();
        $user->name = "admin";
        $user->last_name = "admin";
        $user->phone = "2234564312";
        $user->username = "admin";
        $user->email = "admin@admin.com";
        $user->password = Hash::make('admin');
        $user->save();

        $user = new User();
        $user->name = "Ramiro";
        $user->last_name = "Bacarilli";
        $user->phone = "2234564312";
        $user->username = "rbacarilli";
        $user->email = "bacarilli@bacarilli.com";
        $user->password = Hash::make('rbacarilli');
        $user->save();

        $user = new User();
        $user->name = "Verónica";
        $user->last_name = "De la Canal";
        $user->phone = "2234564312";
        $user->username = "vdelacanal";
        $user->email = "vdelacanal@vdelacanal.com";
        $user->password = Hash::make('vdelacanal');
        $user->save();

        $user = new User();
        $user->name = "Antonela";
        $user->last_name = "Valek";
        $user->phone = "2234564312";
        $user->username = "avalek";
        $user->email = "avalek@avalek.com";
        $user->password = Hash::make('avalek');
        $user->save();

        $user = new User();
        $user->name = "Francisco";
        $user->last_name = "Scarafoni";
        $user->phone = "2234564312";
        $user->username = "fscarafoni";
        $user->email = "fscarafoni@fscarafoni.com";
        $user->password = Hash::make('fscarafoni');
        $user->save();

        $user = new User();
        $user->name = "Macarena";
        $user->last_name = "Renzo";
        $user->phone = "2234564312";
        $user->username = "mrenzo";
        $user->email = "mrenzo@mrenzo.com";
        $user->password = Hash::make('mrenzo');
        $user->save();

    }
}
