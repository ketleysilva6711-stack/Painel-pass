<?php
namespace App\Http\Controllers;
use App\Models\BypassRule;
use Illuminate\Http\Request;

class BypassController extends Controller {
    public function index(Request $request) { return response()->json(['items' => BypassRule::where('user_id', $request->user()->id)->get()]); }
    public function store(Request $request) {
        $v = $request->validate(['rule_name' => 'required', 'target_package' => 'required']);
        $item = BypassRule::create(['user_id' => $request->user()->id, 'rule_name' => $v['rule_name'], 'target_package' => $v['target_package']]);
        return response()->json(['message' => 'Regra de Bypass criada', 'item' => $item], 201);
    }
}