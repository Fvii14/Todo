<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ChangePassMail;
use App\Models\MailTracking;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function showRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return view('auth.passwords.email')->withErrors(['email' => 'No se encontró un usuario con ese correo.']);
        }

        // Eliminar tokens anteriores del usuario
        PasswordResetToken::where('user_id', $user->id)->delete();

        // Crear y guardar el token en la tabla password_reset_tokens
        $token = Str::random(64);
        PasswordResetToken::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $resetLink = url('/passwords/reset/'.$user->id.'/'.$token);

        try {
            Mail::to($user->email)->send(new ChangePassMail($user->name, $resetLink));
            MailTracking::track($user, ChangePassMail::class);
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el correo: '.$e->getMessage());
        }

        return view('user.emailrecuperar-exito', ['email' => $user->email]);
    }

    public function showResetForm($userId, $token)
    {
        $user = User::find($userId);

        if (! $user) {
            return redirect()->route('password.request')->with('error', 'Usuario no válido.');
        }

        $validToken = PasswordResetToken::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();

        if (! $validToken || ! Hash::check($token, $validToken->token)) {
            return redirect()->route('password.request')->with('error', 'Token inválido o expirado.');
        }

        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $user->email,
            'user_id' => $user->id,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'user_id' => 'required|integer',
            'password' => 'required|confirmed|min:8',
            'token' => 'required',
        ]);

        $user = User::where('id', $request->user_id)->where('email', $request->email)->first();

        if (! $user) {
            return redirect()->route('password.request')->with('error', 'Datos inválidos.');
        }

        $tokenRecord = PasswordResetToken::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->first();

        if (! $tokenRecord || ! Hash::check($request->token, $tokenRecord->token)) {
            return redirect()->route('password.request')->with('error', 'Token inválido o expirado.');
        }

        // Restablecer la contraseña
        $user->password = Hash::make($request->password);
        $user->save();

        // Eliminar el token usado
        $tokenRecord->delete();

        return view('auth.passwords.password-update');
    }
}
