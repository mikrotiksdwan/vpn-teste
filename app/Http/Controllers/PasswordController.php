<?php

namespace App\Http\Controllers;

use App\Models\Radcheck;
use App\Mail\RecoveryLinkMail;
use App\Services\SshaHashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = Radcheck::where('username', $credentials['username'])
                        ->where('attribute', 'SSHA-Password')
                        ->first();

        if ($user && SshaHashService::verify($credentials['password'], '{SSHA}' . $user->value)) {
            // Manually log in the user by setting a session variable.
            // We can't use Laravel's Auth since we don't have a standard User model.
            session(['user_email' => $user->email, 'user_logged_in' => true]);
            $request->session()->regenerate();
            return redirect()->intended(route('password.change'));
        }

        return back()->withErrors([
            'username' => 'Usuário ou senha incorretos!',
        ])->onlyInput('username');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        session()->flush();
        return redirect()->route('login');
    }

    /**
     * Show the password change form.
     */
    public function showChangeForm()
    {
        // A simple auth check middleware substitute
        if (!session('user_logged_in')) {
            return redirect()->route('login');
        }
        return view('change', ['email' => session('user_email')]);
    }

    /**
     * Handle a password change request.
     */
    public function changePassword(Request $request)
    {
        if (!session('user_logged_in')) {
            return redirect()->route('login')->with('error', 'Sessão expirada. Faça login novamente.');
        }

        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $newHash = SshaHashService::hash($request->new_password);

        Radcheck::where('email', session('user_email'))
                ->where('attribute', 'SSHA-Password')
                ->update(['value' => $newHash]);

        // Restart FreeRADIUS
        shell_exec('sudo systemctl restart freeradius');

        session()->flush();

        return redirect()->route('login')->with('success', 'Senha alterada com sucesso! Faça o login novamente.');
    }

    /**
     * Show the password recovery request form.
     */
    public function showRecoveryForm()
    {
        return view('recovery');
    }

    /**
     * Handle a password recovery request.
     */
    public function requestRecovery(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->email;

        $user = Radcheck::where('email', $email)->first();

        if ($user) {
            $token = Str::random(60);
            $expires = Carbon::now()->addHour();

            Radcheck::where('email', $email)
                    ->update([
                        'recovery_token' => $token,
                        'token_expires' => $expires
                    ]);

            $recoveryLink = route('password.reset', ['token' => $token]);

            // Note: Mail sending will be logged locally unless mailer is configured.
            Mail::to($email)->send(new RecoveryLinkMail($recoveryLink));
        }

        return redirect()->route('login')->with('info', 'Se um email correspondente for encontrado, um link de recuperação foi enviado. Verifique sua caixa de entrada e spam.');
    }

    /**
     * Show the password reset form.
     */
    public function showResetForm(string $token)
    {
        $user = Radcheck::where('recovery_token', $token)
                        ->where('token_expires', '>', Carbon::now())
                        ->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Token inválido ou expirado. Por favor, solicite um novo link de recuperação.');
        }

        return view('reset', ['token' => $token]);
    }

    /**
     * Handle a password reset request.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Radcheck::where('recovery_token', $request->token)
                        ->where('token_expires', '>', Carbon::now())
                        ->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Token inválido ou expirado. Tente novamente.');
        }

        $newHash = SshaHashService::hash($request->password);

        // Update password and clear token
        Radcheck::where('email', $user->email)
                ->where('attribute', 'SSHA-Password')
                ->update(['value' => $newHash]);

        Radcheck::where('email', $user->email)
                ->update([
                    'recovery_token' => null,
                    'token_expires' => null
                ]);

        // Restart FreeRADIUS
        shell_exec('sudo systemctl restart freeradius');

        return redirect()->route('login')->with('success', 'Sua senha foi redefinida com sucesso! Você já pode fazer login.');
    }
}
