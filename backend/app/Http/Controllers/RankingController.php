<?php

namespace App\Http\Controllers;

use App\Models\Ranking;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function podio()
    {
        return response()->json(Ranking::with('user')->orderBy('points', 'desc')->take(10)->get());
    }
}