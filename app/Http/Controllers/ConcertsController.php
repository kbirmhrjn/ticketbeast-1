<?php

namespace App\Http\Controllers;

use App\Concert;
use Illuminate\Http\Request;

class ConcertsController extends Controller
{
    public function show(Concert $concert)
	{
		return view('concerts.show', compact('concert'));
	}
}
