<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'dni' => 'required|string',
            'domicilioFiscal' => 'required|string',
            'fechaNacimiento' => 'required|date_format:d/m/Y',
            'estadoCivil' => 'required|string',
            'sexo' => 'required|string',
            'casilla435' => 'required|string',
            'casilla460' => 'required|string',
            'noDeudas' => 'required|boolean',
        ]);

        // Crear usuario en `users`
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Usuario registrado con éxito'], 201);
    }
}
