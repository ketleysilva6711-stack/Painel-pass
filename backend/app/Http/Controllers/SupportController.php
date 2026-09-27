<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = SupportTicket::with(['user', 'messages.user']);
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }
        return response()->json($query->orderBy('id', 'desc')->get());
    }

    public function store(Request $request)
    {
        $request->validate(['subject' => 'required|string', 'message' => 'required|string']);
        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'subject' => $request->subject,
            'status' => 'open',
        ]);
        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $request->message,
        ]);
        return response()->json($ticket->load('messages'), 201);
    }
}