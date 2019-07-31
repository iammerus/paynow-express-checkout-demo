<?php

use Illuminate\Database\Seeder;

class FoodItemsTableSeeder extends Seeder
{
    protected $max = 10;

    /**
     * Run the database seeds.
     *
     *
     * @return void
     */
    public function run()
    {
        factory(App\FoodItem::class, $this->max)->create();
    }
}
