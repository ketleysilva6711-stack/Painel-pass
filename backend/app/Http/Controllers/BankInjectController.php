<?php
namespace App\Http\Controllers;
use App\Models\BankInject;
use Illuminate\Http\Request;

class BankInjectController extends Controller {
    public function index(Request $request) { return response()->json(['items' => BankInject::where('user_id', $request->user()->id)->get()]); }
    public function store(Request $request) {
        $v = $request->validate(['bank_name' => 'required', 'inject_type' => 'required']);
        $item = BankInject::create(['user_id' => $request->user()->id, 'bank_name' => $v['bank_name'], 'inject_type' => $v['inject_type']]);
        return response()->json(['message' => 'Inject configurado', 'item' => $item], 201);
    }
}