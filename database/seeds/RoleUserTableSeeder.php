<?php

use Illuminate\Database\Seeder;


class RoleUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Admin
        DB::table('role_user')->insert([
            'role_id' => 1,
            'user_id' => 3,
        ]);

        //Cashier
        for ($i = 3; $i<8; $i++)
            DB::table('role_user')->insert([
                'role_id' => 2,
                'user_id' => $i,
            ]);
        
        //Seller
        for ($i = 3; $i<9; $i++)
            DB::table('role_user')->insert([
                'role_id' => 3,
                'user_id' => $i,
            ]);
        
    }
}
