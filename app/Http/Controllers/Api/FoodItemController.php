<?php

namespace App\Http\Controllers\Api;

use App\FoodItem;
use App\Http\Controllers\Controller;
use BadMethodCallException;
use Illuminate\Http\Request;

class FoodItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return FoodItem[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return FoodItem::paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // TODO: Implement
        throw new BadMethodCallException("Not Implemented");
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\FoodItem $foodItem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FoodItem $foodItem)
    {
        // TODO: Implement
        throw new BadMethodCallException("Not Implemented");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\FoodItem $foodItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(FoodItem $foodItem)
    {
        // TODO: Implement
        throw new BadMethodCallException("Not Implemented");
    }
}
