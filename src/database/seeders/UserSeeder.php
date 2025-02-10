<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('p@ssw0rd!1234'),
        ];
        DB::table('admins')->insert($param);

        $param = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('coachtech1106'),
        ];
        DB::table('users')->insert($param);
    }
}
