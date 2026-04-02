<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Ayuda;
use App\Models\AyudaRequisito;
use App\Models\AyudaSolicitada;
use App\Models\Contratacion;
use App\Models\Product;
use App\Models\UserDocument;
use App\Services\GcsUploaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SolicitudAyudaController extends Controller
{
    public function store(Request $request, $ayuda_id, GcsUploaderService $gcs)
    {
        $user = Auth::user();
        $ayuda = Ayuda::findOrFail($ayuda_id); // $ayuda ya está disponible aquí

        if (! $ayuda->tramitable) {
            return response()->view('user.beneficiario-no-tramitable');
        }

        // 1. Guardar todas las respuestas del cuestionario
        foreach ($request->input('answer', []) as $question_id => $respuesta) {
            Answer::updateOrCreate(
                ['user_id' => $user->id, 'question_id' => $question_id, 'conviviente_id' => null],
                ['answer' => $respuesta]
            );
        }

        // 2. Guardar todos los documentos subidos
        foreach ($request->allFiles() as $key => $file) {
            if (Str::startsWith($key, 'documento_') && $file) {
                $document_id = str_replace('documento_', '', $key);
                $path = "documentos/usuario_{$user->id}/".Str::uuid().'.'.$file->getClientOriginalExtension();
                $gcs->uploadFile($file, $path); // Usando el servicio GCS inyectado
                UserDocument::updateOrCreate(
                    ['user_id' => $user->id, 'document_id' => $document_id],
                    [
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]
                );
            }
        }

        // 3. Procesar y guardar la firma si se envió
        if ($request->filled('firma_base64')) {
            $base64 = $request->input('firma_base64');
            if (Str::startsWith($base64, 'data:image')) {
                $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $base64);
            }
            $binary = base64_decode($base64, true);

            if ($binary === false) {
                return redirect()->back()->with('error', 'La firma no es válida.');
            }
            $filename = "firmas/usuario_{$user->id}/firma_{$user->id}.png";
            $gcs->uploadString($binary, $filename); // Usando el servicio GCS
            UserDocument::updateOrCreate(
                ['user_id' => $user->id, 'document_id' => 3], // Asumiendo document_id 3 es para la firma
                [
                    'file_path' => $filename,
                    'file_name' => "firma_{$user->id}.png",
                    'file_type' => 'image/png',
                    'size' => strlen($binary),
                ]
            );
        }

        // 4. Guardar num_cuenta SÓLO si la ayuda NO es de pago y el campo fue enviado
        // El formulario ya muestra/oculta este campo basado en $ayuda->pago
        if (! (isset($ayuda->pago) && $ayuda->pago == 1)) {
            if ($request->filled('num_cuenta')) {
                $num_cuenta = $request->input('num_cuenta');
                Answer::updateOrCreate(
                    ['user_id' => $user->id, 'question_id' => 88, 'conviviente_id' => null], // Asumiendo ID 88 para num_cuenta
                    ['answer' => $num_cuenta]
                );
            }
        }

        // 5. Verificar los requisitos de la ayuda
        $requisitos = AyudaRequisito::where('ayuda_id', $ayuda_id)->get();
        // Obtener los IDs de las preguntas para las cuales se enviaron respuestas desde el formulario
        $submitted_question_ids = array_map('strval', array_keys($request->input('answer', [])));

        foreach ($requisitos as $requisito) {
            $required_question_id_str = (string) $requisito->question_id;

            // Busca el registro de respuesta que debería haberse creado/actualizado anteriormente
            $answer_record = Answer::where('user_id', $user->id)
                ->whereNotNull('conviviente_id')
                ->where('question_id', $requisito->question_id) // Usa el ID original para la consulta DB
                ->first();

            if (! $answer_record) {
                // No se encontró un registro de respuesta en la BD para esta pregunta requerida.

                // Verificamos si se suponía que esta pregunta debía tener una respuesta del formulario actual
                if (in_array($required_question_id_str, $submitted_question_ids, true)) {
                    // La pregunta SÍ estaba en los datos enviados (es decir, se mostró en el formulario y se esperaba una respuesta),
                    // pero no se encontró/guardó el registro de respuesta. Esto es un error de "datos faltantes".
                    Log::error("SolicitudAyudaController: Se esperaba respuesta para question_id {$required_question_id_str} (enviada en el form), pero no se encontró en BD para user {$user->id}, ayuda {$ayuda_id}.");

                    return redirect()->route('user.home')->with('error', 'Faltan datos para un requisito obligatorio (Pregunta ID: '.$required_question_id_str.'). Por favor, verifica tus respuestas.');
                } else {
                    // La pregunta NO estaba en los datos enviados desde el formulario.
                    // Esto significa que la ayuda actual no incluía esta pregunta, o no se envió respuesta para ella.
                    Log::warning("SolicitudAyudaController: Requisito para question_id {$required_question_id_str} (Ayuda {$ayuda_id}) no tenía una pregunta/respuesta correspondiente en el formulario enviado. Se omite la comprobación de 'existencia de respuesta' para este requisito.");

                    continue;
                }
            }
            if ($answer_record->answer != $requisito->respuesta_expected) {
                $questionText = $requisito->question ? $requisito->question->text : "ID: {$required_question_id_str}";
                $motivo = 'La respuesta para la pregunta <u><b>'.htmlspecialchars($questionText).'</b></u> no coincide con el requisito.';

                return redirect()->route('user.no-beneficiario')->with('motivo', $motivo);
            }
        }

        // 6. Lógica de Contratación y Redirección Final
        if (isset($ayuda->pago) && $ayuda->pago == 1) {
            // AYUDA DE PAGO: Redirigir al StripeController.
            // StripeController se encargará de crear la Contratacion.
            // Solo necesitamos pasar el ayuda_id.
            $producto = $ayuda->productos->first();
            
            if (!$producto) {
                Log::error("SolicitudAyudaController: No se encontró un producto para la ayuda con pago (ID: {$ayuda->id})");
                return redirect()->route('user.home')->with('error', 'No hay productos disponibles para esta ayuda. Por favor, contacta con soporte.');
            }
            
            $productIdParaStripe = $producto->id;
            $stripeRequest = new \Illuminate\Http\Request;
            $stripeRequest->replace([
                'ayuda_id' => $ayuda->id,
                'product_id' => $productIdParaStripe,
            ]);

            $stripeController = app(\App\Http\Controllers\StripeController::class);

            return $stripeController->createCheckoutSession($stripeRequest);

        } else {
            $product = Product::where('ayudas_id', $ayuda_id)->first();
            $productIdParaContratacion = $product ? $product->id : null;

            if (! $product && $productIdParaContratacion === null) {
                Log::info("SolicitudAyudaController: No se encontró un producto para la ayuda no de pago (ID: {$ayuda_id}) al intentar crear Contratacion. Se registrará sin product_id si la BD lo permite.");
            }

            Contratacion::create([
                'user_id' => $user->id,
                'ayuda_id' => $ayuda_id,
                'product_id' => $productIdParaContratacion, // Será null si no se encontró producto
                'fecha_contratacion' => now(),
                'estado' => 'procesando',
                'monto_comision' => 0.00,
                'monto_total_ayuda' => 0.00,
            ]);

            

            return redirect()->route('user.AyudasSolicitadas')->with('success', 'Ayuda solicitada correctamente.');
        }
    }

    public function index()
    {
        $solicitudes = AyudaSolicitada::all();

        // Retornar la vista con las solicitudes
        return view('admin.solicitudes', compact('solicitudes'));
    }
}
