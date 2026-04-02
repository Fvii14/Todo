<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition()
    {
        return [
            'tipo' => $this->faker->randomElement(['general', 'especial', 'mensual', 'interno']),
            'multi_upload' => $this->faker->boolean,
            'por_conviviente' => $this->faker->boolean,
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->slug,
            'description' => $this->faker->sentence,
            'allowed_types' => 'application/pdf, image/jpeg, image/png',
        ];
    }
}
