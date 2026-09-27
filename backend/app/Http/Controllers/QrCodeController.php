<?php
namespace App\Http\Controllers;
use App\Models\QrCodeLink;
use Illuminate\Http\Request;

class QrCodeController extends Controller {
    public function index(Request $request) { return response()->json(['items' => QrCodeLink::where('user_id', $request->user()->id)->get()]); }
    public function store(Request $request) {
        $v = $request->validate(['title' => 'required', 'target_url' => 'required|url']);
        $item = QrCodeLink::create(['user_id' => $request->user()->id, 'title' => $v['title'], 'target_url' => $v['target_url']]);
        return response()->json(['message' => 'QR Code gerado', 'item' => $item], 201);
    }
}