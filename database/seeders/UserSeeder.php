<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'fran_ttf',
                'email' => 'fran@tutramitefacil.es',
                'email_verified_at' => null,
                'password' => Hash::make('TTF25esp.'),
                'remember_token' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'is_admin' => 1,
                'stripe_customer_id' => null,
                'stripe_payment_method' => null,
            ],
            [
                'name' => 'elena_ttf',
                'email' => 'elena@tutramitefacil.es',
                'email_verified_at' => null,
                'password' => Hash::make('TTF25esp.'),
                'remember_token' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'is_admin' => 1,
                'stripe_customer_id' => null,
                'stripe_payment_method' => null,
            ],
            [
                'name' => 'kevin_ttf',
                'email' => 'kevin@tutramitefacil.es',
                'email_verified_at' => null,
                'password' => Hash::make('TTF25esp.'),
                'remember_token' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'is_admin' => 1,
                'stripe_customer_id' => null,
                'stripe_payment_method' => null,
            ],
            [
                'name' => 'pablo_ttf',
                'email' => 'pablo@tutramitefacil.es',
                'email_verified_at' => null,
                'password' => Hash::make('TTF25esp.'),
                'remember_token' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'is_admin' => 1,
                'stripe_customer_id' => null,
                'stripe_payment_method' => null,
            ],
            [
                'name' => 'christian_ttf',
                'email' => 'christian@tutramitefacil.es',
                'email_verified_at' => null,
                'password' => Hash::make('TTF25esp.'),
                'remember_token' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'is_admin' => 1,
                'stripe_customer_id' => null,
                'stripe_payment_method' => null,
            ],
        ]);
    }
}
