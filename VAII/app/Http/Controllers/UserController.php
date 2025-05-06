<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
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

    public function approve(User $user)
    {
        if ($user->is_approved) {
            return back()->with('info', 'Používateľ je už schválený.');
        }

        $user->is_approved = true;
        $user->save();

        return back()->with('success', 'Používateľ bol úspešne schválený.');
    }

    public function toggleActive(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Nemôžeš deaktivovať svoj vlastný účet.');
        }

        if ($user->email === 'admin@example.com') {
            return back()->with('error', 'Tento systémový admin nemôže byť deaktivovaný.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', 'Stav aktivity používateľa bol zmenený.');
    }

}
