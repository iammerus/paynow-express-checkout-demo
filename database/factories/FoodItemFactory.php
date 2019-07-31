<?php

use App\FoodItem;
use Faker\Generator as Faker;

$food_mains = ['Sadza', 'Rice', "Spaghetti", 'Sadza Rezviyo', 'Mashazhare'];
$food_nxaka = ['Mukaka', 'Mufushwa', 'Meatballs', 'Beef', 'Pork', 'Muboora', 'Covo', 'Rape', 'Munyovhi'];


$factory->define(FoodItem::class, function (Faker $faker) use ($food_mains, $food_nxaka) {
    return [
        'title' => array_random($food_mains) . ' and ' . array_random($food_nxaka),
        'description' => $faker->text(80),
        'image_url' => $faker->imageUrl(400, 400, 'food'),
        'price' => $faker->randomFloat('2', 1, 10)
    ];
});
