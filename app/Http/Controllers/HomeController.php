<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if ($request->user()->hasRole(['admin']))
            #return redirect()->route('stock');
            return view('stock.list');
        if ($request->user()->hasRole(['cashier']))
            #return redirect('cash');
            return view('cash.home');
        if ($request->user()->hasRole(['seller']))
           # return redirect('marketplace');
            return view('marketplace.home');
    }
}
