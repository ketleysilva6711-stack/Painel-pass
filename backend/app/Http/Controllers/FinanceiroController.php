<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class FinanceiroController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin' && $request->user()->role !== 'revendedor') {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $query = \App\Models\User::with('plan')
            ->whereNotNull('conta_expira_em')
            ->orderBy('conta_expira_em', 'asc');

        if ($request->user()->role === 'revendedor') {
            $query->where('reseller_id', $request->user()->id);
        }

        return response()->json([
            'subscriptions' => $query->paginate(25),
            'total_revenue' => Plan::sum('price') ?? 0,
            'active_plans' => Plan::where('active', true)->count(),
        ]);
    }
}