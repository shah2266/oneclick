<?php

namespace Database\Factories;

use App\Models\UserTheme;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserThemeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserTheme::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'theme_name' => 'Dark',
            'stylesheet_name' => 'style.css',
            'status' => 1,
        ];
    }

    /**
     * Define the "light" state for the model.
     *
     * @return Factory
     */
    public function light(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'theme_name' => 'Light',
                'stylesheet_name' => 'light.css',
            ];
        });
    }
}
