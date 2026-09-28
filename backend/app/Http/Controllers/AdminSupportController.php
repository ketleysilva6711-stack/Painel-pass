<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\Request;

class AdminSupportController extends Controller
{
    public function show($id)
    {
        $ticket = SupportTicket::with(['user', 'messages.user'])->findOrFail($id);
        return response()->json($ticket);
    }

    public function addMessage(Request $request, $id)
    {
        $request->validate(['message' => 'required|string']);
        $ticket = SupportTicket::findOrFail($id);

        $payload = [
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $request->message,
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('support_messages', 'is_admin')) {
            $payload['is_admin'] = true;
        }
        $message = SupportMessage::create($payload);

        return response()->json($message, 201);
    }
}
