<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Item;

class WelcomeController extends Controller
{

    public function index()
    {
        $items = Item::orderBy('updated_at', 'desc')-> paginate(20);    // Item からupdated_at順に20個ずつ取り出し
        return view('welcome', [ 'items' => $items, ]);                 // item をTopページに表示

    }
}