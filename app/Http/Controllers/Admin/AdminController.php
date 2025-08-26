<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Radcheck;
use App\Mail\RecoveryLinkMail;
use App\Services\SshaHashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard with a list of all users.
     */
    public function dashboard()
    {
        $users = Radcheck::where('attribute', 'SSHA-Password')
                         ->orderBy('username')
                         ->get();

        return view('admin.dashboard', ['users' => $users]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.invite');
    }

    /**
     * Store a newly created user in storage and send an invitation link.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:radcheck,username',
            'email' => 'required|email|unique:radcheck,email',
        ]);

        $user = Radcheck::create([
            'username' => $request->username,
            'email' => $request->email,
            'attribute' => 'SSHA-Password',
            'op' => ':=',
            'value' => SshaHashService::hash(Str::random(40)), // Long random password
            'is_admin' => false,
        ]);

        // Generate a token for the new user to set their password
        $token = Str::random(60);
        $user->recovery_token = $token;
        $user->token_expires = Carbon::now()->addHours(24);
        $user->save();

        // Send the invitation email
        $invitationLink = route('password.reset', ['token' => $token]);
        Mail::to($user->email)->send(new RecoveryLinkMail($invitationLink));

        return redirect()->route('admin.dashboard')->with('success', 'Convite enviado com sucesso para ' . $user->email);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Radcheck $user)
    {
        // Prevent an admin from deleting their own account
        if ($user->email === session('user_email')) {
            return back()->with('error', 'Você não pode excluir sua própria conta.');
        }

        // Delete all records associated with this username to ensure a full cleanup.
        Radcheck::where('username', $user->username)->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Usuário ' . $user->username . ' excluído com sucesso.');
    }

    /**
     * Promote a user to be an admin.
     */
    public function promote(Radcheck $user)
    {
        $user->is_admin = true;
        $user->save();

        return redirect()->route('admin.dashboard')->with('success', 'Usuário ' . $user->username . ' promovido a admin.');
    }
}
