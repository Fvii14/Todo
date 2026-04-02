<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductosSeeder extends Seeder
{
    public function run()
    {
        DB::table('products')->insert([
            [
                'ayudas_id' => 1,
                'product_name' => 'Cheque Bebé',
                'stripe_product_id' => 'prod_Sba2uZCmCXxVUA',
                'price_id' => '',
                'price' => 24.99,
                'currency' => 'eur',
                'payment_type' => 'one_time',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ayudas_id' => 43,
                'product_name' => 'Ayuda Tarxeta Benvida',
                'stripe_product_id' => 'prod_Sba6LUyp0TQ9zQ',
                'price_id' => '',
                'price' => 24.99,
                'currency' => 'eur',
                'payment_type' => 'one_time',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ayudas_id' => 42,
                'product_name' => 'Ayuda 500€ mes por hijo Madrid',
                'stripe_product_id' => 'prod_Sba6LUyp0TQ9zQ',
                'price_id' => '',
                'price' => 24.99,
                'currency' => 'eur',
                'payment_type' => 'one_time',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ayudas_id' => 2,
                'product_name' => 'Ayuda Alquiler',
                'stripe_product_id' => null,
                'price_id' => '',
                'price' => 0.00,
                'currency' => 'eur',
                'payment_type' => 'annual',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ayudas_id' => 26,
                'product_name' => 'Ingreso Mínimo Vital',
                'stripe_product_id' => null,
                'price_id' => '',
                'price' => 0.00,
                'currency' => 'eur',
                'payment_type' => 'annual',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
