<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function home()
    {
        return view('home', [
            'title' => 'Home',
        ]);
    }

    public function about()
    {
        return view('about', [
            'title' => 'About',
        ]);
    }
}