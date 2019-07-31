<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\View\View;

class PagesController extends Controller
{
    /**
     * The default route of the website
     *
     * @return View|Response
     */
    public function index()
    {
        return view('spa.index');
    }
}
