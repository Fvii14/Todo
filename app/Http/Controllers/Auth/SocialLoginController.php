<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'password' => bcrypt(Str::random(24)),
                    'provider_id' => $googleUser->getId(),
                    'provider' => 'google',
                    'email_verified_at' => now(),
                    'ref_by' => Cookie::get('ref_code'),
                ]
            );

            Auth::login($user, true);

            // Opcional: eliminar cookie tras usarla
            Cookie::queue(Cookie::forget('ref_code'));

            return redirect('/RegisterCollector');
        } catch (\Exception $e) {
            Log::error('Google Auth Error: '.$e->getMessage());

            return redirect('/login')->with('error', 'Error al autenticar con Google');
        }
    }
}
