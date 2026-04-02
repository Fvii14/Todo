<?php

namespace App\Http\Controllers;

use App\Models\Ayuda;
use App\Models\Ccaa;
use App\Models\Contratacion;
use App\Models\ContratacionPagos;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class AdminCobrosController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->whereNotNull('stripe_customer_id');

        // Filtro por tipo de ayuda
        if ($request->filled('ayuda_id')) {
            $query->whereHas('contrataciones', function ($q) use ($request) {
                $q->where('ayuda_id', $request->ayuda_id);
            });
        }

        // Filtro por CCAA
        if ($request->filled('comunidad_autonoma')) {
            $query->whereHas('taxInfo', function ($q) use ($request) {
                $q->whereRaw('LOWER(comunidad_autonoma) = ?', [strtolower($request->comunidad_autonoma)]);
            });
        }

        // Filtro por búsqueda directa
        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->whereHas('taxInfo', function ($q) use ($busqueda) {
                $q->where('full_name', 'like', "%$busqueda%")
                    ->orWhere('nif', 'like', "%$busqueda%")
                    ->orWhere('telefono', 'like', "%$busqueda%");
            });
        }

        $users = $query->get();
        $ayudas = Ayuda::orderBy('nombre_ayuda')->get();
        $ccaa = Ccaa::orderBy('nombre_ccaa')->get();

        return view('admin.cobros', compact('users', 'ayudas', 'ccaa'));
    }

    public function chargeSelectedUser(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.5',
        ]);

        $user = User::findOrFail($request->user_id);
        $amountCents = intval($request->amount * 100);

        try {
            $intent = PaymentIntent::create([
                'amount' => $amountCents,
                'currency' => 'eur',
                'customer' => $user->stripe_customer_id,
                'payment_method' => $user->stripe_payment_method,
                'off_session' => true,
                'confirm' => true,
                'description' => 'Cobro manual por administración',
            ]);
            Payment::create([
                'payment_id' => $intent->id,
                'amount' => $request->amount,
                'currency' => $intent->currency,
                'status' => $intent->status,
                'email' => $user->email,
            ]);
            $contratacion = Contratacion::where('user_id', $user->id)
                ->whereHas('estadosContratacion', fn ($q) => $q->where('codigo', 'OP1-Tramitacion'))
                ->latest()
                ->first();

            if ($contratacion) {
                ContratacionPagos::create([
                    'contratacion_id' => $contratacion->id,
                    'payment_id' => $intent->id,
                ]);
            }

            return back()->with('success', "Cobro de {$request->amount} € a {$user->email} realizado con éxito.");
        } catch (\Stripe\Exception\CardException $e) {
            return back()->with('error', "Error al cobrar a {$user->email}: ".$e->getError()->message);
        }
    }
}
