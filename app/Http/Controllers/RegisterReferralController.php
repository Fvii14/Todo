<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class RegisterReferralController extends Controller
{
    public function showRegistrationForm(Request $request)
    {
        // Si ya está logueado, redirigir
        if (Auth::check()) {
            return redirect()->route('user.home');
        }

        // Si viene con ref_code en URL, guardar en cookie
        if ($request->has('ref_code')) {
            Cookie::queue('ref_code', $request->input('ref_code'), 43200); // 30 días
        }

        return view('auth.passwords.new-account');
    }
}
