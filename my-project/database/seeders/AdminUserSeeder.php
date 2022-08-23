<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {

        $defaultPassword = 'please_change_me!';
        $userList = [
            [
                'name' => 'admin',
                'email' => 'bis@bis.com',
                'is_admin' => true,
                'password' => bcrypt($defaultPassword)
            ],
        ];

        DB::table('users')->insert($userList);
    }
}
