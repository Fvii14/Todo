<?php

namespace App\Http\Controllers;

use App\Models\Ayuda;
use App\Models\Contratacion;
use App\Models\ContratacionPagos;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session;
use Stripe\Price;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeController extends Controller
{
    /**
     * Convierte el precio de centavos a euros para guardar en Payment.
     *
     * @param  float|null  $priceInCents  Precio en centavos
     * @return float Precio en euros
     */
    private function convertCentsToEuros($priceInCents): float
    {
        if ($priceInCents === null || $priceInCents == 0) {
            return 0.0;
        }

        return round($priceInCents / 100, 2);
    }

    public function createCheckoutSession(Request $request)
    {
        // Log para verificar la entrada del request
        Log::info('Iniciando la creación de la sesión de Stripe', ['request' => $request->all()]);
        $user = Auth::user();
        // Verificar si el usuario está autenticado y obtener su email
        $email = auth()->check() ? $user->email : $request->input('email');

        // Obtener el ID del producto desde el cuerpo de la solicitud
        $productId = $request->input('product_id');
        Log::info('Producto ID recibido:', ['product_id' => $productId]);
        // Guardar la ID del producto en la sesión
        // session(['product_id' => $productId]);
        // Buscar el producto en la base de datos
        $producto = Product::find($productId);
        if (! $producto) {
            Log::error('Producto no encontrado para el ID:', ['product_id' => $productId]);

            return response()->json(['error' => 'Producto no encontrado'], 404);
        }
        Log::info('Datos completos del producto:', $producto->toArray());

        $payment_type = $producto->payment_type;

        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        try {

            // Log para verificar el tipo de pago
            Log::info('Tipo de pago seleccionado', ['payment_type' => $payment_type]);

            // Para pagos anuales (o únicos)
            if ($payment_type == 'annual' || $payment_type == 'one-time') {
                Log::info('Procesando pago único o anual', ['producto' => $producto->product_name]);

                // Crear una nueva sesión de pago de Stripe Checkout
                $session = Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [
                        [
                            'price' => $producto->price_id,
                            'quantity' => 1,
                        ],
                    ],

                    'mode' => 'payment', // Pago único
                    // Cambiar success_url para pasar el session_id como un parámetro de la URL
                    'success_url' => route('operation.success').'?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('user.home').'?session_id={CHECKOUT_SESSION_ID}',
                    'customer_email' => $email,
                    'allow_promotion_codes' => true,  // Email del usuario
                ]);
                // El precio ya está en centavos en la BD, convertir a euros para guardar
                $priceInEuros = $this->convertCentsToEuros($producto->price);

                // Guardar el pago en la base de datos
                $payment = Payment::create([
                    'payment_id' => $session->id,  // ID de la sesión de Stripe
                    'status' => 'pending',  // Inicialmente el pago está pendiente
                    'amount' => $priceInEuros,
                    'currency' => 'eur',  // La moneda utilizada
                    'email' => $email,  // Email del cliente
                    'product_id' => $producto->id, // ID del producto en la BBDD
                ]);

                // Log para verificar la creación de la sesión
                Log::info('Sesión de Stripe creada correctamente', ['session_id' => $session->id]);
                $id_session = $session->id;

                return redirect()->away($session->url);
                /* return response()->json(['id' => $session->id]); */
            }
            // Para pagos mensuales (suscripciones)
            elseif ($payment_type == 'monthly') {
                Log::info('Procesando suscripción mensual', ['producto' => $producto->product_name]);

                // El precio ya está en centavos en la BD (normalizado por ProductService)
                $priceInCents = (int) round($producto->price);
                Log::info('Precio para suscripción (ya en centavos)', [
                    'precio_centavos' => $priceInCents,
                ]);

                // Crear un price_id en Stripe para suscripción
                $price = Price::create([
                    'unit_amount' => $priceInCents,
                    'currency' => 'eur',
                    'recurring' => ['interval' => 'month'],  // Pagos mensuales
                    'product' => $producto->stripe_product_id,  // El ID del producto de Stripe
                ]);

                // Crear una sesión de suscripción con el price_id
                $session = Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [
                        [
                            'price' => $price->id,  // Usar el price_id creado
                            'quantity' => 1,
                        ],
                    ],
                    'mode' => 'subscription',  // Suscripción recurrente
                    // Cambiar success_url para pasar el session_id como un parámetro de la URL
                    'success_url' => route('payment.success').'?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('payment.cancel').'?session_id={CHECKOUT_SESSION_ID}',
                    'customer_email' => $email,  // Email del usuario
                ]);
                // El precio ya está en centavos en la BD, convertir a euros para guardar
                $priceInEuros = $this->convertCentsToEuros($producto->price);

                // Guardar el pago en la base de datos
                $payment = Payment::create([
                    'payment_id' => $session->id,  // ID de la sesión de Stripe
                    'status' => 'pending',  // Inicialmente el pago está pendiente
                    'amount' => $priceInEuros,
                    'currency' => 'eur',  // La moneda utilizada
                    'email' => $email,  // Email del cliente
                    'product_id' => $producto->id,
                    'user_id' => $user->id ?? null,
                ]);

                // Log para verificar la creación de la sesión
                Log::info('Sesión de Stripe para suscripción creada correctamente', ['session_id' => $session->id]);

                return response()->json(['id' => $session->id]);
            } elseif ($payment_type == 'one_time') {
                Log::info('Procesando pago único', ['producto' => $producto->product_name]);

                // Crear una nueva sesión de pago de Stripe Checkout
                $session = Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [
                        [
                            'price' => $producto->price_id,
                            'quantity' => 1,
                        ],
                    ],
                    'mode' => 'payment', // Pago único
                    'success_url' => route('operation.success').'?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('user.home').'?session_id={CHECKOUT_SESSION_ID}',
                    'customer_email' => $email,
                    'allow_promotion_codes' => true,
                ]);

                // El precio ya está en centavos en la BD, convertir a euros para guardar
                $priceInEuros = $this->convertCentsToEuros($producto->price);

                // Guardar el pago en la base de datos
                Payment::create([
                    'payment_id' => $session->id,
                    'status' => 'pending',
                    'amount' => $priceInEuros,
                    'currency' => 'eur',
                    'email' => $email,
                    'product_id' => $producto->id,
                ]);

                return redirect()->away($session->url);
            } else {
                // payment_type erroneo
                Log::error('Tipo de pago no válido', ['payment_type' => $payment_type]);

                return response()->json(['error' => 'Tipo de pago no válido'], 404);
            }
        } catch (\Exception $e) {
            // Log del error si ocurre un fallo en Stripe
            Log::error('Error al crear la sesión de Stripe', ['error' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // Método para manejar el éxito del pago
    // Este método se llama cuando el pago se completa con éxito

    public function success(Request $request)
    {
        Log::info('Llegamos al método success');
        // Obtener el session_id de la URL (query string)
        $sessionId = $request->query('session_id');  // Usamos query en vez de input

        if (! $sessionId) {
            Log::error('session_id no encontrado en la URL');

            return response()->json(['error' => 'session_id no encontrado en la URL'], 400);
        }

        // Obtener la sesión de Stripe
        try {
            // Configurar Stripe con tu clave secreta
            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            // Obtener la sesión de Stripe usando el session_id
            $session = Session::retrieve($sessionId);
            Log::info('Sesión de Stripe obtenida', ['session_id' => $sessionId]);

            // Aquí puedes actualizar el estado del pago en tu base de datos
            $payment = Payment::where('payment_id', $session->id)->first();
            if ($payment) {
                $payment->status = 'completed';  // Marcar el pago como completado
                $payment->save();
            }

            // Obtener información adicional de la sesión
            $paymentIntent = \Stripe\PaymentIntent::retrieve($session->payment_intent);
            $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentIntent->payment_method);

            // Obtener el producto relacionado
            $product = Product::find($payment->product_id);

            if (! $product) {
                Log::error('Producto no encontrado en success()', ['product_id' => $payment->product_id]);
            } else {
                Log::info('Producto recuperado correctamente', ['product_id' => $product->id]);
            }
            $user = User::find($payment->user_id);
            $ayuda = Ayuda::find($product->ayudas_id);

            // Crear el registro en la tabla contrataciones
            $contratacion = Contratacion::create([
                'user_id' => $user->id,
                'stripe_payment_method' => $paymentMethod->id,
                'card_last4' => $paymentMethod->card->last4,
                'card_brand' => $paymentMethod->card->brand,
                'card_exp_month' => $paymentMethod->card->exp_month,
                'card_exp_year' => $paymentMethod->card->exp_year,
                'card_funding' => $paymentMethod->card->funding,
                'product_id' => $product->id,
                'ayuda_id' => $product->ayudas_id,
                'fecha_contratacion' => now(),
            ]);
            app(\App\Services\EstadoContratacionService::class)->syncEstadosByCodigos($contratacion, ['OP1-Documentacion'], false);
            // Crear el registro en la tabla contratacion_pagos
            ContratacionPagos::create([
                'contratacion_id' => $contratacion->id,
                'payment_id' => $payment->payment_id,
            ]);

            try {
                $user = auth()->user();
                $ayuda = Ayuda::find($product->ayudas_id);

                Log::info('La ayuda contratada es la:', ['slug' => $ayuda->slug]);

                // Emails
                if ($ayuda->slug === 'bono_cultural_joven_2025') {
                    Mail::to($user->email)->send(new \App\Mail\BonoCulturalDocumentacionMail($user, 1));
                    \App\Models\MailTracking::track($user, \App\Mail\BonoCulturalDocumentacionMail::class, 1);
                } else {
                    Mail::to($user->email)->send(new \App\Mail\ContratacionMail($user->name, $ayuda, 1));
                    \App\Models\MailTracking::track($user, \App\Mail\ContratacionMail::class, 1);
                }

                // Teléfono
                $telefono = \App\Models\Answer::where('user_id', $user->id)
                    ->where('question_id', 45)
                    ->whereNull('conviviente_id')
                    ->value('answer');

                $telefono = preg_replace('/[^0-9]/', '', $telefono);
                if (! str_starts_with($telefono, '34')) {
                    $telefono = '34'.$telefono;
                }

                // Brevo
                $brevoService = app(\App\Services\BrevoService::class);
                $brevoService->updateContact([
                    'email' => $user->email,
                    'PRECIO_AYUDA' => number_format($ayuda->cuantia_usuario, 0, ',', '.'),
                    'NOMBRE_AYUDA' => $ayuda->nombre_ayuda,
                ]);
                $brevoService->sendWhatsAppMessageWithParams($telefono, 370, [
                    'NOMBRE' => $user->name,
                    'NOMBRE_AYUDA' => $ayuda->nombre_ayuda,
                ]);
            } catch (\Throwable $e) {
                Log::warning('No se pudo enviar el email o WhatsApp en success(): '.$e->getMessage());
            }

            // Retornar la vista con la sesión y el pago completado
            return view('user.pago-success', [
                'stripe_session_data' => $session, // Opcional: pasa la sesión de Stripe si la necesitas en la vista
                'message' => '¡Tu pago ha sido procesado con éxito!', // Un mensaje para tu vista
                // Puedes pasar cualquier otra variable que tu vista 'user.pago-success' necesite
            ]);
        } catch (\Exception $e) {
            Log::error('Error al recuperar la sesión de Stripe', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Error al recuperar la sesión de Stripe'], 500);
        }
    }

    // Método para manejar la cancelación del pago
    public function cancel(Request $request)
    {
        // Obtener el session_id de la URL (query string)
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            Log::error('session_id no encontrado en la URL');

            return response()->json(['error' => 'session_id no encontrado en la URL'], 400);
        }

        // Obtener la sesión de Stripe
        try {
            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            $session = Session::retrieve($sessionId);
            Log::info('Sesión de Stripe obtenida', ['session_id' => $sessionId]);

            // Actualizar el estado del pago en la base de datos
            $payment = Payment::where('payment_id', $session->id)->first();
            if ($payment) {
                $payment->status = 'canceled';  // Marcar el pago como cancelado
                $payment->save();
            }

            // Retornar una vista con el mensaje adecuado
            return view('stripe.cancel', ['message' => 'El pago no se ha completado. Inténtalo de nuevo más tarde.']);
        } catch (\Exception $e) {
            Log::error('Error al recuperar la sesión de Stripe', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Error al recuperar la sesión de Stripe'], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        // Configuración de la clave secreta de Stripe
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
        // Obtenemos el contenido del Webhook (el cuerpo del POST)
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature'); // Obtén la firma del Webhook
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');  // Define la clave secreta de tu Webhook

        // Verifica la firma del Webhook para asegurarnos de que es Stripe quien está enviando el evento
        try {
            // $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
            $event = json_decode($payload);
            Log::info('Webhook recibido y verificado correctamente', ['event_type' => $event->type]);
        } catch (SignatureVerificationException $e) {
            Log::error('Firma del Webhook no válida', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Firma no válida'], 400);
        }

        // Procesamos los eventos que Stripe nos envíe
        try {
            switch ($event->type) {
                case 'checkout.session.completed':
                    // Evento cuando el pago se completa correctamente
                    $session = $event->data->object;  // Obtén la sesión de pago
                    Log::info('Evento checkout.session.completed', ['session_id' => $session->id]);

                    // Actualiza el estado de pago en la base de datos
                    $payment = Payment::where('payment_id', $session->id)->first();
                    if ($payment) {
                        $payment->status = 'completed';
                        $payment->save();
                        Log::info('Pago completado correctamente', ['payment_id' => $session->id]);
                    } else {
                        Log::error('Pago no encontrado para la sesión', ['session_id' => $session->id]);
                    }
                    break;

                case 'payment_intent.payment_failed':
                    // Evento cuando el pago falla
                    $paymentIntent = $event->data->object;  // Obtén la información del payment intent
                    Log::info('Evento payment_intent.payment_failed', ['payment_intent_id' => $paymentIntent->id]);

                    // Actualiza el estado de pago en la base de datos
                    $payment = Payment::where('payment_id', $paymentIntent->id)->first();
                    if ($payment) {
                        $payment->status = 'failed';
                        $payment->save();
                        Log::info('Pago fallido', ['payment_id' => $paymentIntent->id]);
                    } else {
                        Log::error('Pago no encontrado para el intent', ['payment_intent_id' => $paymentIntent->id]);
                    }
                    break;

                case 'checkout.session.async_payment_failed':
                    // Evento cuando el pago asincrónico falla
                    $session = $event->data->object;  // Obtén la sesión de pago asincrónico
                    Log::info('Evento checkout.session.async_payment_failed', ['session_id' => $session->id]);

                    // Actualiza el estado de pago en la base de datos
                    $payment = Payment::where('payment_id', $session->id)->first();
                    if ($payment) {
                        $payment->status = 'failed';
                        $payment->save();
                        Log::info('Pago asincrónico fallido', ['payment_id' => $session->id]);
                    } else {
                        Log::error('Pago asincrónico no encontrado para la sesión', ['session_id' => $session->id]);
                    }
                    break;

                default:
                    Log::info('Evento Stripe no procesado', ['event_type' => $event->type]);
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar el evento de Stripe', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Error al procesar el evento'], 500);
        }

        // Retorna una respuesta exitosa
        return response()->json(['status' => 'success']);
    }
}
