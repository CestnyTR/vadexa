<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalController extends Controller
{
    public function about(): View
    {
        return view('legal.about');
    }

    public function contact(): View
    {
        return view('legal.contact');
    }

    public function privacy(): View
    {
        return view('legal.privacy');
    }

    public function distanceSales(): View
    {
        return view('legal.distance-sales');
    }

    public function deliveryReturns(): View
    {
        return view('legal.delivery-returns');
    }
}
