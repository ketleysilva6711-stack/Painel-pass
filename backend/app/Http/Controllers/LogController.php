<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }
        $logs = Log::with('user')->orderBy('id', 'desc')->paginate(50);
        return response()->json($logs);
    }
}