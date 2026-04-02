<?php

namespace App\Http\Controllers;

use App\Models\Ayuda;
use App\Models\Contratacion;
use App\Models\ContratacionPagos;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Customer;
use Stripe\PaymentIntent; // Aunque no se use explícitamente, Auth::user() devuelve una instancia de User
use Stripe\PaymentMethod;
use Stripe\SetupIntent;
use Stripe\Stripe; // <--- Añadido para obtener el producto

class StripeSetupController extends Controller
{
    public function showForm(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $user = Auth::user();

        if (! $user->stripe_customer_id) {
            Log::info('Cliente Stripe no encontrado, creando uno nuevo.');
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name,
            ]);
            $user->stripe_customer_id = $customer->id;
            $user->save();
        }

        $setupIntent = SetupIntent::create([
            'customer' => $user->stripe_customer_id,
        ]);

        $paymentMethodStripe = $user->stripe_payment_method;
        $isUpdating = $paymentMethodStripe ? true : false;
        $retrievedPaymentMethod = null;

        if ($paymentMethodStripe) {
            try {
                $retrievedPaymentMethod = PaymentMethod::retrieve($paymentMethodStripe);
            } catch (\Exception $e) {
                Log::error('Error al recuperar el método de pago de Stripe: '.$e->getMessage());
                // Considera invalidar el método de pago guardado si ya no es válido en Stripe
                // $user->stripe_payment_method = null;
                // $user->save();
                // $isUpdating = false;
            }
        }

        return view('stripe.setup', [
            'clientSecret' => $setupIntent->client_secret,
            'paymentMethod' => $retrievedPaymentMethod,
            'isUpdating' => $isUpdating,
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Guardando método de pago y creando contratación.');
        $request->validate([
            'payment_method' => 'required|string',
            'ayuda_id' => 'required|integer|exists:ayudas,id', // Validar que ayuda_id exista
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
        $user = Auth::user();
        $user->stripe_payment_method = $request->input('payment_method');
        $user->save();

        $paymentMethodId = $request->input('payment_method');
        $stripePaymentMethod = PaymentMethod::retrieve($paymentMethodId);
        $card = $stripePaymentMethod->card;

        $ayudaId = $request->input('ayuda_id');
        $ayuda = Ayuda::find($ayudaId);
        $productId = null;

        if ($ayuda) {
            $product = $ayuda->products->first(); // Asumiendo que 'products' es la relación hasMany y se espera uno
            if ($product) {
                $productId = $product->id;
            } else {
                Log::warning("STORE: La Ayuda con ID {$ayudaId} no tiene un producto asociado en la tabla products.");
            }
        } else {
            Log::error("STORE: No se pudo encontrar la Ayuda con ID: {$ayudaId}. No se asignará product_id.");
            // Podrías retornar un error aquí si la ayuda es crucial y no se encuentra
            // return redirect()->back()->withErrors(['ayuda_id' => 'La ayuda especificada no es válida.']);
        }

        $contratacion = Contratacion::create([
            'user_id' => $user->id,
            'ayuda_id' => $ayudaId,
            'product_id' => $productId, // <--- ID del producto
            'stripe_payment_method' => $paymentMethodId,
            'card_last4' => $card->last4,
            'card_brand' => $card->brand,
            'card_exp_month' => $card->exp_month,
            'card_exp_year' => $card->exp_year,
            'card_funding' => $card->funding,
            'fecha_contratacion' => now(),
            'estado' => 'procesando',
            'monto_comision' => 0.00,
            'monto_total_ayuda' => 0.00,
        ]);

        Pago::create([
            'contratacion_id' => $contratacion->id,
            'monto' => 0.00,
            'estado' => 'pendiente',
            'respuesta_stripe' => null,
            'fecha_pago' => null,
        ]);

        return redirect()->route('user.rellenarayuda', ['id' => $ayudaId])
            ->with('success', 'Método de pago guardado correctamente. Ahora puedes completar los datos de la ayuda.');
    }

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            return response('Webhook error: '.$e->getMessage(), 400);
        }

        if ($event->type === 'setup_intent.succeeded') {
            // $intent = $event->data->object;
            // Aquí podrías actualizar algo si quieres, por ejemplo, el estado de la contratación
            // o registrar que el SetupIntent fue exitoso para el usuario.
        }

        return response('Webhook handled', 200);
    }

    public function chargeUser(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $user = Auth::user();
        $amount = $request->input('amount');
        $amountCents = intval($amount * 100);

        try {
            $intent = PaymentIntent::create([
                'amount' => $amountCents,
                'currency' => 'eur',
                'customer' => $user->stripe_customer_id,
                'payment_method' => $user->stripe_payment_method,
                'off_session' => true,
                'confirm' => true,
                'description' => 'Cobro automático TuTrámiteFácil',
            ]);

            $contratacion = Contratacion::where('user_id', $user->id)
                ->where('estado', 'procesando') // O el estado que corresponda
                ->latest('fecha_contratacion') // Para obtener la más reciente si hay varias
                ->first();

            if ($contratacion) {
                ContratacionPagos::create([
                    'contratacion_id' => $contratacion->id,
                    'payment_id' => $intent->id,
                ]);
                // Aquí podrías actualizar el estado de la contratación a 'pagado' o similar
                // y el estado del Pago asociado.
            } else {
                Log::error("CHARGEUSER: No se encontró una contratación en estado 'procesando' para el user_id: {$user->id} para asociar el PaymentIntent {$intent->id}.");
            }

            return response()->json([
                'success' => true,
                'message' => 'Pago realizado con éxito',
                'payment_intent_id' => $intent->id,
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            Log::error("CHARGEUSER: Error de tarjeta para user_id {$user->id}: ".$e->getError()->message);

            // Aquí también podrías querer actualizar el estado de la contratación a 'fallido'
            return response()->json([
                'success' => false,
                'error' => $e->getError()->message,
            ], 402); // 402 Payment Required, o un código de error apropiado
        } catch (\Exception $e) {
            Log::error("CHARGEUSER: Error general para user_id {$user->id}: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Ocurrió un error al procesar el pago.',
            ], 500);
        }
    }

    public function editPaymentMethod(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $user = Auth::user();

        // Es importante asegurarse de que el usuario tenga un stripe_customer_id
        if (! $user->stripe_customer_id) {
            // Manejar el caso: quizás redirigir o mostrar un error.
            // Por ahora, se intentará crear uno si no existe, como en showForm.
            Log::info('EDITPAYMENTMETHOD: Cliente Stripe no encontrado para user_id {$user->id}, creando uno nuevo.');
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name,
            ]);
            $user->stripe_customer_id = $customer->id;
            $user->save();
        }

        $setupIntent = SetupIntent::create([
            'customer' => $user->stripe_customer_id,
        ]);

        $paymentMethodStripe = $user->stripe_payment_method;
        $isUpdating = $paymentMethodStripe ? true : false;
        $retrievedPaymentMethod = null;

        if ($paymentMethodStripe) {
            try {
                $retrievedPaymentMethod = PaymentMethod::retrieve($paymentMethodStripe);
            } catch (\Exception $e) {
                Log::error('Error al recuperar el método de pago de Stripe en editPaymentMethod: '.$e->getMessage());
            }
        }

        return view('stripe.cambiar-tarjeta', [
            'clientSecret' => $setupIntent->client_secret,
            'paymentMethod' => $retrievedPaymentMethod,
            'isUpdating' => $isUpdating,
        ]);
    }

    public function updatePaymentMethod(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            // Si se crea una contratación aquí, ayuda_id también debería ser requerida y validada
            'ayuda_id' => 'nullable|integer|exists:ayudas,id', // 'nullable' si no siempre se crea una contratación o si ayuda_id es opcional
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));
        $user = Auth::user();
        $paymentMethodId = $request->input('payment_method');

        $stripePaymentMethod = PaymentMethod::retrieve($paymentMethodId);
        $card = $stripePaymentMethod->card;

        // Actualizar en la tabla users
        $user->stripe_payment_method = $paymentMethodId;
        $user->save();

        // Si se debe crear una nueva contratación al actualizar el método de pago:
        $ayudaId = $request->input('ayuda_id');
        $productId = null;

        if ($ayudaId) { // Solo intentar obtener product_id si se proporciona ayuda_id
            $ayuda = Ayuda::find($ayudaId);
            if ($ayuda) {
                $product = $ayuda->products->first();
                if ($product) {
                    $productId = $product->id;
                } else {
                    Log::warning("UPDATEPAYMENTMETHOD: La Ayuda con ID {$ayudaId} no tiene un producto asociado.");
                }
            } else {
                Log::error("UPDATEPAYMENTMETHOD: No se pudo encontrar la Ayuda con ID: {$ayudaId}.");
            }
        }

        if ($ayudaId) {
            Contratacion::create([
                'user_id' => $user->id,
                'stripe_payment_method' => $paymentMethodId,
                'card_last4' => $card->last4,
                'card_brand' => $card->brand,
                'card_exp_month' => $card->exp_month,
                'card_exp_year' => $card->exp_year,
                'card_funding' => $card->funding,
                'fecha_contratacion' => now(),
                'ayuda_id' => $ayudaId, // ID de la Ayuda
                'product_id' => $productId, // <--- ID del producto
                'estado' => 'procesando',
                'monto_comision' => 0.00,
                'monto_total_ayuda' => 0.00,
            ]);

            return redirect('/home')->with('success', 'Tu tarjeta ha sido actualizada y se ha registrado la información de la nueva ayuda.');
        }

        return redirect('/home')->with('success', 'Tu tarjeta ha sido actualizada correctamente.');
    }
}
