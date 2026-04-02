<?php

// Define el archivo de log
$logDir = __DIR__.'/../../../storage/logs/';  // Ruta de la carpeta
if (! file_exists($logDir)) {
    mkdir($logDir, 0777, true);  // Crea la carpeta si no existe
}
$logFile = $logDir.'stripe.log';  // Ruta del archivo de log

writeToLog('Inicio', $logFile);
// Cargar el archivo .env
loadEnv(__DIR__.'/../../../.env');
// require 'vendor/autoload.php';
require_once __DIR__.'/../../../vendor/autoload.php';
// Cargar el archivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../../../');
$dotenv->load();

// Configura la clave secreta de Stripe
\Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));  // Usar la clave secreta de Stripe desde .env
if (getenv('STRIPE_SECRET_KEY') === false) {
    writeToLog('API key is missing from the .env file', $logFile);  // Registrar el error en el log antes de detener el script
    exit('API key is missing from the .env file');  // Detener la ejecución si la clave está ausente
} else {
    writeToLog('API key NO missing from the .env file', $logFile);
}

// El secreto del webhook
$endpoint_secret = getenv('STRIPE_WEBHOOK_SECRET');  // Usar el webhook secreto desde .env
// Obtén los datos del cuerpo de la solicitud
$input = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

// Verificar si hay datos en la solicitud
if (! $input) {
    writeToLog('[ERROR] No se recibió payload en el webhook', $logFile);
    http_response_code(400);
    writeToLog('No se recibió payload', $logFile);
}

// Registrar el cuerpo recibido para depuración
writeToLog('[INFO] Payload recibido: '.$input, $logFile);

// Revisar si el header HTTP_STRIPE_SIGNATURE está presente
if (! isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
    writeToLog('[ERROR] Header HTTP_STRIPE_SIGNATURE no está presente', $logFile);
    http_response_code(400);
    writeToLog('Header HTTP_STRIPE_SIGNATURE no encontrado', $logFile);
}

try {
    // Verifica la firma del webhook usando la clave secreta del endpoint
    $event = \Stripe\Webhook::constructEvent($input, $sig_header, $endpoint_secret);
} catch (\UnexpectedValueException $e) {
    // El payload no es válido
    http_response_code(400);
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // La firma no es válida
    http_response_code(400);
    exit();
}
$session = null;
$paymentIntent = null;
$customerId = null;
// Maneja el evento
switch ($event->type) {

    case 'payment_intent.succeeded':
        /* $paymentIntent = $event->data->object; // contiene un objeto PaymentIntent
         writeToLog("Intento correcto: " . json_encode($paymentIntent));
         // Verificar si el customer_id está presente
         $customerId = $paymentIntent->customer;
         $receiptEmail = $paymentIntent->receipt_email;
         if (!$customerId) {
             writeToLog('No customer_id found in paymentIntent, creating a new customer.');
             // Crear un cliente en Stripe si no existe
             $customer = \Stripe\Customer::create(['email' => $receiptEmail]);
             $customerId = $customer->id;
             if (!$customerId) {
                 writeToLog('Error: customer_id es NULL o es invalido.');
                 return;
             }else{
                 writeToLog('El customerId es: '. $customerId);
             }

         }*/
        break;

    case 'charge.succeeded':
        $charge = $event->data->object; // contiene un objeto Charge
        writeToLog('Cargo completado con éxito: '.json_encode($charge), $logFile);
        break;

    case 'invoice.payment_succeeded':
        $invoice = $event->data->object; // contiene un objeto Invoice
        writeToLog('Factura pagada con éxito: '.json_encode($invoice), $logFile);
        /*
        // Si la factura está asociada a un PaymentIntent, puedes intentar acceder al producto desde ahí
        if (isset($invoice->payment_intent)) {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($invoice->payment_intent);
            //$productId = getProductIdFromPaymentIntent($paymentIntent);
            writeToLog("ID del producto desde Invoice: " . $productId);
        }*/
        break;
    case 'checkout.session.completed':
        $session = $event->data->object;
        // Obtener el customerId desde la sesión
        $customerId = $session->customer ?? null;
        $customerEmail = $session->customer_details->email ?? null;
        // Si no hay customerId, crear un nuevo cliente
        if (! $customerId && $customerEmail) {
            writeToLog('No customer_id found in session, creating a new customer.');
            $customer = \Stripe\Customer::create(['email' => $customerEmail]);
            $customerId = $customer->id ?? null;

            if (! $customerId) {
                writeToLog('Error: customer_id es NULL o inválido.');

                return;
            } else {
                writeToLog('Nuevo customerId creado: '.$customerId);
            }
        } else {
            writeToLog('El customerId es antes de pasarlo a la funcion es: '.$customerId);
            writeToLog('El email es antes de pasarlo a la funcion es: '.$customerEmail);
        }

        // Retrieve line items
        $lineItems = \Stripe\Checkout\Session::retrieve([
            'id' => $session->id,
            'expand' => ['line_items'],
        ])->line_items;

        writeToLog('Checkout Session: '.json_encode($session));
        writeToLog('Checkout Session items: '.json_encode($lineItems));
        saveCheckoutSessionData($session, $customerId);
        break;

    default:
        // Maneja otros tipos de eventos
        break;
}

// Responde al webhook de Stripe con un código 200 para confirmar que lo recibimos correctamente
http_response_code(200);

// Función para cargar las variables de entorno desde el archivo .env
function loadEnv($path)
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        putenv("{$name}={$value}");
    }
}
// Función para escribir en el log
function writeToLog($message, $logFile)
{
    $date = date('Y-m-d H:i:s');
    $logMessage = "[{$date}] - {$message}\n";
    error_log($logMessage, 3, $logFile);
}

function saveCheckoutSessionData($session, $customerId)
{
    writeToLog('Iniciando la función saveCheckoutSessionData.', $logFile);

    // Validar sesión
    if (! $session || ! $session->id) {
        writeToLog('Error: La sesión no es válida.', $logFile);

        return;
    }

    // Validar el Customer ID
    if (! $customerId) {
        writeToLog('Error: customer_id no válido.', $logFile);

        return;
    }

    // Datos de la sesión
    $customerEmail = $session->customer_details->email ?? 'Desconocido';
    $customerName = $session->customer_details->name ?? 'Desconocido';
    $amountTotal = $session->amount_total / 100;  // Convertir a euros
    $currency = strtoupper($session->currency);
    $paymentStatus = $session->payment_status;
    $intentId = $session->payment_intent;

    if ($intentId === null) {
        // Si no hay payment_intent, intentamos obtenerlo de otras fuentes
        if ($session->mode === 'subscription') {
            // Para suscripciones, el intent podría estar en la factura
            try {
                $subscription = \Stripe\Subscription::retrieve($session->subscription);
                $latestInvoice = \Stripe\Invoice::retrieve($subscription->latest_invoice);
                $intentId = $latestInvoice->payment_intent;
            } catch (\Exception $e) {
                writeToLog('Error retrieving subscription or invoice: '.$e->getMessage(), $logFile);
            }
        } elseif ($session->mode === 'setup') {
            // Para modo setup, usamos el setup_intent
            $intentId = $session->setup_intent;
        }

    }

    // Obtener los items de la sesión de checkout
    $lineItems = \Stripe\Checkout\Session::retrieve([
        'id' => $session->id,
        'expand' => ['line_items'],
    ])->line_items;

    if (! empty($lineItems->data)) {
        $firstItem = $lineItems->data[0];

        // Obtener el ID y el nombre del producto
        $productId = $firstItem->price->product ?? 'Desconocido';
        $productName = $firstItem->price->nickname;

        if ($productName === null) {
            $productName = $session->metadata['Id_producto'];
        }

        // Crear la conexión a la base de datos
        $host = 'localhost';  // Cambiar si es necesario
        $dbname = 'stripe';
        $username = 'root';  // Cambiar si es necesario
        $password = '';  // Cambiar si es necesario

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Preparar la consulta de inserción para la tabla de pagos
            $stmt = $pdo->prepare('INSERT INTO payments (
                                    stripe_payment_intent_id,
                                    customer_id,
                                    amount_received,
                                    currency,
                                    payment_status,
                                    payment_method,
                                    receipt_email,
                                    customer_name,
                                    product_name,
                                    product_id,
                                    created_at
                                    )
                                    VALUES (:stripe_payment_intent_id,
                                            :customer_id,
                                            :amount_received,
                                            :currency,
                                            :payment_status,
                                            :payment_method,
                                            :receipt_email,
                                            :customer_name,
                                            :product_name,
                                            :product_id,
                                            :created_at)');

            // Asignar los valores correspondientes a los parámetros
            $stmt->execute([
                ':stripe_payment_intent_id' => $intentId,
                ':customer_id' => $customerId,
                ':amount_received' => $amountTotal,
                ':currency' => $currency,
                ':payment_status' => $paymentStatus,
                ':payment_method' => $session->payment_link,
                ':receipt_email' => $customerEmail,
                ':customer_name' => $customerName,
                ':product_name' => $productName,
                ':product_id' => $productId,
                ':created_at' => date('Y-m-d H:i:s', $session->created),
            ]);

            writeToLog('Datos de pago guardados en la base de datos.', $logFile);

        } catch (PDOException $e) {
            writeToLog('Error al guardar los datos del pago: '.$e->getMessage(), $logFile);
        }
    } else {
        writeToLog('No se encontraron items en la sesión.', $logFile);
    }
}
