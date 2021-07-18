<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::forceCreate([
            'email' => 'jeremy.bloomstrom@gmail.com',
            'name' => 'Jeremy Bloomstrom',
            'password' => bcrypt('P@ssw0rd')
        ]);
    }
}
