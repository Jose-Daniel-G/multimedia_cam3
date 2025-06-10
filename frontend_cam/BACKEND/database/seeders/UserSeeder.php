<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'=>'admin',
            'organismo_id'=> 11,
            'email'=> 'admin.admin@gmail.com',
            'email_verified_at' => now(),
            'password'=> bcrypt('admin'),
        ])->assignRole('admin');

        User::create([
            'name'=>'Jose Daniel Grijalba Osorio',
            'organismo_id'=> 16,
            'email'=> 'jose.grijalba04@gmail.com',
            'email_verified_at' => now(),
            'password'=> bcrypt('123123123'),
        ])->assignRole('admin');

        User::create([
            'name'=>'Daniel Osorio',
            'organismo_id'=> 16,
            'email'=> 'jose.jdgo97@gmail.com',
            'email_verified_at' => now(),
            'password'=> bcrypt('123123123'),
        ])->assignRole('admin');


        User::create([
            'name'=>'Juan David Grijalba Osorio',
            'organismo_id'=> 11,
            'email'=> 'juandavidgo1997@gmail.com',
            'email_verified_at' => now(),
            'password'=> bcrypt('123123123'),
        ])->assignRole('funcionario');

        User::create([
            'name'=>'Hebron funcionario',
            'organismo_id'=> 11,
            'email'=> 'hebron.customer@gmail.com',
            'email_verified_at' => now(),
            'password'=> bcrypt('123123123'),
        ])->assignRole('funcionario');

        User::create([
            'name'=>'Mario',
            'organismo_id'=> 11,
            'email'=> 'mario@gmail.com',
            'email_verified_at' => now(),
            'password'=> bcrypt('123123123'),
        ])->assignRole('funcionario');

        User::create([
            'name'=>'Alejandro',
            'organismo_id'=> 11,
            'email'=> 'alejo@gmail.com',
            'email_verified_at' => now(),
            'password'=> bcrypt('123123123'),
        ])->assignRole('funcionario');
        User::create([
            'name'=>'Luigi Mangione',
            'organismo_id'=> 11,
            'email'=> 'luigi7@gmail.com',
            'email_verified_at' => now(),
            'password'=> bcrypt('123123123'),
        ])->assignRole('funcionario');

        User::factory(9)->create();
    }
}
