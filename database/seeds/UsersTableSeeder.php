<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'courier',
            'email' => 'courier@laundry.com',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
            'role' => 3
        ]);
    }
}
