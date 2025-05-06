<?php

namespace App\Http\Controllers;

use App\Mail\MessageReplyMail;
use Illuminate\Http\Request;
use App\Models\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth; // Pridané pre kontrolu prihláseného používateľa
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|min:10|max:1000',
        ]);

        Message::create([
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message,
        ]);

        return redirect()->back()->with('success', 'Message sent successfully!');
    }



    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string',
        ]);

        $message = Message::findOrFail($id);
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'You must be logged in to reply.'], 403);
        }

        Mail::to($message->email)->send(new MessageReplyMail(
            $message->name,
            $message->message,
            $request->reply,
            $user->name,
            $user->email
        ));

        $message->replied = true;
        $message->save();

        return response()->json(['success' => true]);
    }





    public function index()
    {
        $messages = Message::latest()->get();
        return view('products.messages', compact('messages'));
    }
}
