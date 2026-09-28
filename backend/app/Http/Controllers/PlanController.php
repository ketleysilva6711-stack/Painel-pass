<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        return response()->json(Plan::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:plans,slug',
            'price' => 'required|numeric|min:0',
            'limits' => 'nullable|array',
            'features' => 'nullable|array',
            'active' => 'nullable|boolean',
        ]);

        $plan = Plan::create($request->all());
        return response()->json($plan, 201);
    }

    public function show($id)
    {
        return response()->json(Plan::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:plans,slug,' . $id,
            'price' => 'sometimes|required|numeric|min:0',
            'limits' => 'sometimes|nullable|array',
            'features' => 'sometimes|nullable|array',
            'active' => 'sometimes|nullable|boolean',
        ]);
        $plan->update($request->all());
        return response()->json($plan);
    }

    public function destroy($id)
    {
        Plan::findOrFail($id)->delete();
        return response()->json(['message' => 'Plano removido.']);
    }
}
