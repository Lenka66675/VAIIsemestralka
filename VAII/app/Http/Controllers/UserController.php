<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Iba admin vidí všetkých používateľov
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function toggleRole(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Nemôžeš si zmeniť vlastnú rolu.');
        }
        if ($user->email === 'admin@example.com') {
            return back()->with('error', 'Tomuto používateľovi nemožno zmeniť rolu.');
        }

        $user->role = $user->role === 'admin' ? 'user' : 'admin';
        $user->save();

        return back()->with('success', 'Rola bola zmenená na: ' . $user->role);
    }
}

