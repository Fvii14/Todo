<?php

use App\Enums\QuestionnaireTipo;
use App\Events\EventUserRegistered;
use App\Helpers\SimulationHelper;
use App\Http\Controllers\Admin\AyudasRequisitosJsonController;
use App\Http\Controllers\Admin\QuestionConditionController;
use App\Http\Controllers\Admin\UserSimulationController;
use App\Http\Controllers\AdminBusquedaController;
use App\Http\Controllers\AdminCobrosController;
use App\Http\Controllers\AdminEstadoController;
use App\Http\Controllers\AdminUserPanelController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\AyudaDatosController;
use App\Http\Controllers\AyudaDocumentoController;
use App\Http\Controllers\AyudaDocumentoConvivienteController;
use App\Http\Controllers\AyudaRecursoController;
use App\Http\Controllers\AyudaRequisitoController;
use App\Http\Controllers\AyudasController;
use App\Http\Controllers\AyudasPosiblesController;
use App\Http\Controllers\AyudasShowController;
use App\Http\Controllers\AyudasSolicitadasController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContratacionController;
use App\Http\Controllers\ConvivienteFormularioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\GestionAyudasController;
use App\Http\Controllers\HistorialAyudasController;
use App\Http\Controllers\HistorialPagosController;
use App\Http\Controllers\LeadMagnetController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OnboarderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuestionCategoryController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionnaireAnswerController;
use App\Http\Controllers\QuestionnaireController;
use App\Http\Controllers\QuestionnaireQuestionController;
use App\Http\Controllers\QuestionPurposeController;
use App\Http\Controllers\RegisterReferralController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\SolicitudAyudaController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\StripeSetupController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDocumentController;
use App\Http\Controllers\UserInfoController;
use App\Http\Controllers\WizardController;
use App\Http\Middleware\UserLoggedAndInitialFormDone;
use App\Mcp\Servers\N8NServer;
use App\Models\Answer;
use App\Models\Ayuda;
use App\Models\AyudaPreRequisito;
use App\Models\AyudaSolicitada;
use App\Models\Ccaa;
use App\Models\CrmStateHistory;
use App\Models\Municipio;
use App\Models\Provincia;
use App\Models\Question;
use App\Models\QuestionCondition;
use App\Models\Questionnaire;
use App\Models\QuestionnaireDraft;
use App\Models\User;
use App\Models\UserAyuda;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

Route::post('/logout', function () {
    Auth::logout();

    return redirect('/');
})->name('logout');

Route::get('/logout', function () {
    Auth::logout();

    return redirect('/');
})->name('logout');

Route::get('/login', function (Request $request) {
    if (auth()->check()) {

        $onboarderEmails = ['fm_453212@hotmail.com', 'cotarelorg@gmail.com', 'kekah_htinha@hotmail.com', 'moisesbusiness30@gmail.com', 'fritz07_7@hotmail.com', 'melani.arcosb@gmail.com', 'sheila.g690@gmail.com', 'ismaelpuli92@gmail.com', 'olenakuzmych25@gmail.com', 'jhoan.valencia.torres@gmail.com', 'salvadoramos1996@gmail.com', 'xuancarcorredor@gmail.com', 'sharon.pz@hotmail.com', 'dante091289@icloud.com', 'egarciapino@gmail.com', 'ivansorokin111@gmail.com', 'alfredoooescuderooo@gmail.com', 'lauraduranfernandez1@gmail.com', 'amandaalcocer1998@gmail.com', 'antiamanuelaraujo@gmail.com', 'martina.juan.pereira@gmail.com', 'jenniherranz13@gmail.com', 'crispypinero@gmail.com', 'srodriguezrendo@gmail.com', 'beatriz_91@live.co', 'andres_quishpe@hotmail.com', 'sergiogeorgievs@gmail.com', 'luisneftalirodriguez@gmail.com', 'nurbel12@icloud.com', 'ingridescanio92@gmail.com', 'laurahernandezpalanco@gmail.com', 'marioquirant18@gmail.com', 'juliocesarabad67@gmail.com', 'ariadna_4-9-1995@hotmail.com', 'maikel960520@gmail.com', 'raquemagia@gmail.com', 'alicianatividadnebreda@gmail.com', 'alicia.borona05@gmail.com', 'caataa144@gmail.com', 'marrolodosa@gmail.com', 'morenachannels36@gmail.com', 'rubenmupri@gmail.com', 'julianbarahona1971@gmail.com', 'patriciamorvay@gmail.com', 'alisshounnytiti@gmail.com', 'beatriz_91@live.com', 'ismael_puli@hotmail.com', 'chenoaalaminos21@gmail.com', 'nklappenbach@gmail.com', 'jeanfernando1905@gmail.com', 'lauraduranfernandez1@gmial.com', 'andreailin2003@yahoo.es', 'saraaraque1998@icloud.com', 'mariacandelamartinez1998@gmail.com', 'nuki101012@gmail.com', 'mam-en@hotmail.com'];

        $userEmail = strtolower(trim(auth()->user()->email));

        if (in_array($userEmail, array_map('strtolower', $onboarderEmails))) {
            return redirect()->route('onboarder');
        }

        return redirect()->route('user.home');
    }

    if ($request->has('ref_code')) {
        Cookie::queue('ref_code', $request->input('ref_code'), 43200);
    }

    return view('auth.registerv4');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $onboarderEmails = ['fm_453212@hotmail.com', 'cotarelorg@gmail.com', 'kekah_htinha@hotmail.com', 'moisesbusiness30@gmail.com', 'fritz07_7@hotmail.com', 'melani.arcosb@gmail.com', 'sheila.g690@gmail.com', 'ismaelpuli92@gmail.com', 'olenakuzmych25@gmail.com', 'jhoan.valencia.torres@gmail.com', 'salvadoramos1996@gmail.com', 'xuancarcorredor@gmail.com', 'sharon.pz@hotmail.com', 'dante091289@icloud.com', 'egarciapino@gmail.com', 'ivansorokin111@gmail.com', 'alfredoooescuderooo@gmail.com', 'lauraduranfernandez1@gmail.com', 'amandaalcocer1998@gmail.com', 'antiamanuelaraujo@gmail.com', 'martina.juan.pereira@gmail.com', 'jenniherranz13@gmail.com', 'crispypinero@gmail.com', 'srodriguezrendo@gmail.com', 'beatriz_91@live.co', 'andres_quishpe@hotmail.com', 'sergiogeorgievs@gmail.com', 'luisneftalirodriguez@gmail.com', 'nurbel12@icloud.com', 'ingridescanio92@gmail.com', 'laurahernandezpalanco@gmail.com', 'marioquirant18@gmail.com', 'juliocesarabad67@gmail.com', 'ariadna_4-9-1995@hotmail.com', 'maikel960520@gmail.com', 'raquemagia@gmail.com', 'alicianatividadnebreda@gmail.com', 'alicia.borona05@gmail.com', 'caataa144@gmail.com', 'marrolodosa@gmail.com', 'morenachannels36@gmail.com', 'rubenmupri@gmail.com', 'julianbarahona1971@gmail.com', 'patriciamorvay@gmail.com', 'alisshounnytiti@gmail.com', 'beatriz_91@live.com', 'ismael_puli@hotmail.com', 'chenoaalaminos21@gmail.com', 'nklappenbach@gmail.com', 'jeanfernando1905@gmail.com', 'lauraduranfernandez1@gmial.com', 'andreailin2003@yahoo.es', 'saraaraque1998@icloud.com', 'mariacandelamartinez1998@gmail.com', 'nuki101012@gmail.com', 'mam-en@hotmail.com'];

        $userEmail = strtolower(trim(Auth::user()->email));

        if (in_array($userEmail, array_map('strtolower', $onboarderEmails))) {
            return redirect()->route('onboarder');
        }

        return redirect()->intended('/dashboard');
    }

    return back()->withErrors([
        'email' => 'Las credenciales no coinciden con nuestros registros.',
    ]);
});

Route::get('/register', function () {
    return view('auth.register');
});

Route::get('/registerv2', function () {
    return view('auth.registerv2');
});

Route::get('/registerv3', function () {
    return view('auth.registerv3', [
        'username' => auth()->user()->name,
        'email' => auth()->user()->email,
    ]);
})->middleware('auth');

// Página de pruebas del chatbot Dify
Route::get('/test-chatbot', function () {
    return view('test-chatbot');
})->name('test.chatbot');

// PANTALLAS BUENAS SEGÚN CANVA

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('user.home');
    }

    return view('auth.registerv4');
})->middleware(['auth', UserLoggedAndInitialFormDone::class])->name('user.root');

Route::get('/RegisterCollector', function () {
    $user = Auth::user();
    // * Comprobamos que el usuario no haya hecho el collector en los últimos 6 meses
    $date_collector = Answer::where('user_id', $user->id)
        ->where('question_id', 90)
        ->whereNull('conviviente_id')
        ->first();

    if ($user->is_admin && ! session('is_simulating', false)) {
        return redirect()->route('admin.dashboardv2');
    }

    if ($user->taxInfo()->exists() || ($date_collector && Carbon::parse($date_collector->answer)->gte(Carbon::now()->subMonths(6)))) {

        return redirect()->route('user.home');
    }

    return view('auth.registercollector');
})->middleware('auth')->name('registercollector');

Route::get('/RegisterAuto', function () {
    $user = Auth::user();
    // * Comprobamos que el usuario no haya hecho el collector en los últimos 6 meses
    $date_collector = Answer::where('user_id', $user->id)
        ->where('question_id', 90)
        ->whereNull('conviviente_id')
        ->first();

    if ($user->taxInfo()->exists() || ($user->is_admin && ! session('is_simulating', false)) || ($date_collector && Carbon::parse($date_collector->answer)->gte(Carbon::now()->subMonths(6)))) {
        return redirect()->route('user.home');
    }

    $token = env('BANKFLIP_API_KEY');

    $headers = [
        'Accept: application/json',
        "Authorization: Bearer {$token}",
        'Content-Type: application/json',
    ];

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://'.env('BANKFLIP_HOST').'/session',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode([
            'requests' => [
                [
                    'model' => [
                        'type' => 'SEG_SOCIAL_VIDA_LABORAL',
                    ],
                ],
                [
                    'model' => [
                        'type' => 'HACIENDA_RENTA',
                    ],
                ],
            ],
            'integrationMode' => 'iframe',
        ]),
        CURLOPT_HTTPHEADER => $headers,
    ]);

    $response = curl_exec($curl);

    return view('auth.registerauto', [
        'username' => $user->name,
        'email' => $user->email,
        'iframe' => json_decode($response)->widgetLink,
    ]);
})->middleware('auth')->name('registerauto');

Route::get('/dashboard/logicacuestionarios/{id}', function ($id) {
    $user = Auth::user();
    if (! $user->is_admin) {
        return redirect()->route('user.home');
    }

    $questionnaire = Questionnaire::findOrFail($id);
    $transiciones = QuestionCondition::with(['question', 'nextQuestion'])
        ->where('questionnaire_id', $questionnaire->id)
        ->get();

    return view('admin.questionnaireslogic', compact('transiciones', 'questionnaire'));
})->middleware('auth')->name('logicacuestionarios');

Route::get('/registerwithoutclave', function () {
    $questionnaire = Questionnaire::with('questions')->findOrFail(1);
    $questions = $questionnaire->questions;

    return view('registerwithoutclave', [
        'username' => auth()->user()->name,
        'questionnaire' => $questionnaire,
        'questions' => $questions,
    ]);
})->middleware('auth')->name('registerwithoutclave');

Route::post('/savecollector', [AnswerController::class, 'collector'])->middleware('auth')->name('savecollector');
Route::get('/dashboard/cuestionarios', [QuestionnaireController::class, 'index'])->middleware('auth')->name('questionnaires.index');
Route::get('/dashboard/userDetail/{id}', [UserController::class, 'showAnswers'])->middleware('auth')->name('admin.userDetail');
Route::get('/dashboard/ayudas', [AyudasController::class, 'index'])->middleware('auth')->name('ayudas.index');
Route::post('/ayudarequisito', [AyudaRequisitoController::class, 'store'])->middleware('auth')->name('ayudarequisito.store');
Route::get('/dashboard/requisitos', [AyudaRequisitoController::class, 'index'])->middleware('auth')->name('ayudarequisito.index');
Route::post('/questionnaires', [QuestionnaireController::class, 'store'])->middleware('auth')->name('questionnaires.store');
Route::post('/dashboard/ayudas', [AyudasController::class, 'store'])->name('ayudas.store');
Route::put('/ayudas/{id}', [AyudasController::class, 'update'])->name('ayudas.update');
Route::delete('/questionnaires/{id}', [QuestionnaireController::class, 'destroy'])->middleware('auth')->name('questionnaires.destroy');
Route::delete('/ayudas/{id}', [AyudasController::class, 'destroy'])->middleware('auth')->name('ayudas.destroy');
Route::delete('/ayudarequisito/{id}', [AyudaRequisitoController::class, 'destroy'])->middleware('auth')->name('ayudarequisito.destroy');
Route::put('/questionnaires/{id}', [QuestionnaireController::class, 'update'])->middleware('auth')->name('questionnaires.update');
Route::put('/question/{id}', [QuestionController::class, 'update'])->middleware('auth')->name('question.update');
Route::post('/question', [QuestionController::class, 'store'])->middleware('auth')->name('question.store');
Route::delete('/question/{id}', [QuestionController::class, 'destroy'])->middleware('auth')->name('question.destroy');
Route::get('/questionnaire/{id}', [QuestionnaireController::class, 'showQuestionnaire']);
Route::post('/questionnaire/{id}/complete', [QuestionnaireController::class, 'completeQuestionnaire']);
Route::post('/answers/store', [QuestionnaireAnswerController::class, 'store'])->name('answers.store');
Route::post('/ayudadocumento', [AyudaDocumentoController::class, 'store'])->name('ayudadocumento.store');
Route::delete('/ayudadocumento/{id}', [AyudaDocumentoController::class, 'destroy'])->name('ayudadocumento.destroy');
Route::post('/ayudadocumentoconviviente', [AyudaDocumentoConvivienteController::class, 'store'])->name('ayudadocumentoconviviente.store');
Route::delete('/ayudadocumentoconviviente/{id}', [AyudaDocumentoConvivienteController::class, 'destroy'])->name('ayudadocumentoconviviente.destroy');
Route::get('/ayudadocumentoconviviente/ayuda/{ayudaId}', [AyudaDocumentoConvivienteController::class, 'getByAyuda'])->name('ayudadocumentoconviviente.getByAyuda');
Route::delete('/documento/{id}', [DocumentoController::class, 'destroy'])->name('documento.destroy');
Route::post('/documento', [DocumentoController::class, 'store'])->name('documento.store');
Route::post('/questionnaire-questions', [QuestionnaireQuestionController::class, 'store'])->name('questionnairequestion.store');
Route::delete('/questionnaire-questions/{id}/{otherid}', [QuestionnaireQuestionController::class, 'destroy'])->name('questionnairequestion.destroy');

Route::get('/home', action: [AyudasPosiblesController::class, 'index'])->middleware(['auth', UserLoggedAndInitialFormDone::class])->name('user.home');

Route::get('/dashboard/solicitudes', [SolicitudAyudaController::class, 'index'])->middleware('auth')->name('admin.solicitudes');

// FIN PANTALLAS BUENAS SEGÚN CANVA

Route::get('/dashboardv2', function () {
    $user = Auth::user();

    if ($user->is_admin) {
        $search = request('search');
        $query = User::query();
        $totalUsers = User::where('is_admin', false)->count();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->where('is_admin', false)->paginate(15)->withQueryString();

        $userIds = $users->pluck('id');

        $answers = Answer::whereIn('user_id', $userIds)->whereNull('conviviente_id')->get()->groupBy('user_id');
        $usersWithCollectorReal = Answer::where('question_id', 90)
            ->whereNull('conviviente_id')
            ->whereNotNull('answer')
            ->where('answer', '!=', '')
            ->distinct('user_id')
            ->count('user_id');

        $usersWithTaxInfo = User::has('taxInfo')->count();
        $answersByUser = Answer::whereNull('conviviente_id')
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        return view('admin.dashboard', compact('users', 'answers', 'answersByUser', 'usersWithCollectorReal', 'usersWithTaxInfo', 'totalUsers'));
    }

    return redirect()->route('user.home');
})->middleware('auth')->name('dashboardv2');

Route::get('/ayudas-solicitadas', [AyudasSolicitadasController::class, 'index'])->middleware('auth')->name('user.AyudasSolicitadas');
Route::post('/convivientes', [AyudasSolicitadasController::class, 'storeConviviente'])->name('conviviente.store');
Route::get('/form-conviviente/{questionnaireId}/{index}', [AyudasSolicitadasController::class, 'showConvivientes'])->name('form-conviviente.show');

// Dashboard principal
Route::get('/dashboard', function () {
    $user = Auth::user();
    if (! $user->is_admin) {
        return redirect()->route('user.home');
    }

    return app(DashboardController::class)->index();
})->middleware('auth')->name('admin.dashboardv2');

// Historial de documentos
Route::get('/dashboardv2/docs-history', function (Request $request) {
    $user = Auth::user();
    if (! $user->is_admin) {
        return redirect()->route('user.home');
    }

    return app(UserDocumentController::class)->index($request);
})->middleware('auth')->name('admin.docs-history');

// Actualizar estado de documento
Route::patch('/user-documents/{userDocument}', function (Request $request, \App\Models\UserDocument $userDocument) {
    $user = Auth::user();
    if (! $user->is_admin) {
        abort(403);
    }

    return app(UserDocumentController::class)->update($request, $userDocument);
})->middleware('auth')->name('admin.user-documents.update');

Route::middleware('auth')->delete('/user-documents/{userDocument}', [UserDocumentController::class, 'destroy'])->name('user-documents.destroy');

// Bandeja de trabajo
Route::get('/dashboardv2/work-tray', function () {
    $user = Auth::user();
    if (! $user->is_admin) {
        return redirect()->route('user.home');
    }

    return app(DashboardController::class)->workTray();
})->middleware('auth')->name('admin.work-tray');

// Posibles Beneficiarios
Route::middleware('auth')->prefix('/dashboard/posibles-beneficiarios')->group(function () {
    Route::get('/', [\App\Http\Controllers\PosiblesBeneficiariosController::class, 'index'])
        ->name('posibles-beneficiarios.index');
    Route::post('/generar-reporte', [\App\Http\Controllers\PosiblesBeneficiariosController::class, 'generar'])
        ->name('posibles-beneficiarios.generar');
    Route::post('/descargar-csv', [\App\Http\Controllers\PosiblesBeneficiariosController::class, 'descargarCsv'])
        ->name('posibles-beneficiarios.descargar-csv');
});

Route::middleware('auth')->group(function () {
    Route::get('/admin/tramitacion/users/{user}/contrataciones', [\App\Http\Controllers\ContratacionController::class, 'listByUser'])
        ->name('admin.tramitacion.user.contrataciones');
});

// Historial de expedientes
Route::get('/dashboard/historial-expedientes', function (Request $request) {
    $user = Auth::user();
    if (! $user->is_admin) {
        return redirect()->route('user.home');
    }

    return app(ContratacionController::class)->index($request);
})->middleware('auth')->name('admin.historialexpedientes');

Route::get('/api/ayudas-por-ccaa/{ccaa_id}', function (Request $request, $ccaa_id) {
    $user = Auth::user();
    if (! $user->is_admin) {
        abort(403);
    }

    return app(ContratacionController::class)->getAyudasPorCcaa($ccaa_id);
})->middleware('auth')->name('api.ayudas.por.ccaa');

Route::get('/api/contrataciones-usuario/{user_id}', function (Request $request, $user_id) {
    $user = Auth::user();
    if (! $user->is_admin) {
        abort(403);
    }

    return app(ContratacionController::class)->getContratacionesUsuario($user_id, $request);
})->middleware('auth')->name('api.contrataciones.usuario');

// Cambio de estado/fase de contratación: solo vía PATCH /contrataciones/{id}/estados-opx

// Rutas para tareas de contratación
Route::get('/contrataciones/{contratacion}/datos-actualizados', [ContratacionController::class, 'getDatosActualizados'])->middleware('auth')->name('contrataciones.datos.actualizados');
// Ruta para procesar documentos extra
Route::post('/contrataciones/{contratacionId}/documentos-extra', [ContratacionController::class, 'procesarDocumentosExtra'])->middleware('auth')->name('contrataciones.documentos-extra');

Route::get('/dashboardv2/modificar', [App\Http\Controllers\AyudasController::class, 'editarFechasYEstado'])->name('ayudas.editar');
Route::get('/dashboardv2/recursos', [App\Http\Controllers\AyudaRecursoController::class, 'index'])->name('ayudas.recursos');
Route::get('/dashboardv2/recursos/{id}/edit', [App\Http\Controllers\AyudaRecursoController::class, 'edit'])->name('ayudas.recursos.edit');
Route::get('/dashboardv2/recursos/{id}/create', [App\Http\Controllers\AyudaRecursoController::class, 'create'])->name('ayudas.recursos.create');
Route::post('/dashboardv2/recursos/{id}', [App\Http\Controllers\AyudaRecursoController::class, 'store'])->name('ayudas.recursos.store');

Route::put('/admin/ayudas/{id}', [App\Http\Controllers\AyudasController::class, 'updateFechasYEstado'])->name('ayudas.update');

Route::patch('/contrataciones/{id}/tramitador', [ContratacionController::class, 'changeTramitador'])->name('contrataciones.changeTramitador');

Route::patch(
    'contrataciones/{id}/update-datos',
    [ContratacionController::class, 'updateDatos']
);

// Listado de clientes
Route::get('/dashboard/clientes', function (Request $request) {
    $user = Auth::user();
    if (! $user->is_admin) {
        return redirect()->route('user.home');
    }

    return app(ClienteController::class)->index($request);
})->middleware('auth')->name('admin.clientes.index');

// DESACOPLADO: apartado usuarios / users-history
// Route::get('/dashboardv2/users-history', [UserController::class, 'index'])
//     ->name('admin.users-history')
//     ->middleware('auth');

// Route::post('/users/{user}/notas', [UserController::class, 'storeNota'])
//     ->name('admin.users.notas.store')
//     ->middleware('auth');

Route::prefix('admin')->middleware('auth')->group(function () {
    // Rutas para productos
    Route::prefix('products')->name('admin.products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{id}', [ProductController::class, 'show'])->name('show');
        Route::patch('/{id}', [ProductController::class, 'update'])->name('update');
    });

    Route::prefix('ayudas/{ayudaId}/products')->name('admin.ayudas.products.')->group(function () {
        Route::post('/', [ProductController::class, 'associateToAyuda'])->name('associate');
        Route::get('/', [ProductController::class, 'getByAyuda'])->name('get');
    });

    // Rutas para servicios
    Route::prefix('servicios')->name('admin.servicios.')->group(function () {
        Route::get('/', [ServicioController::class, 'index'])->name('index');
        Route::post('/', [ServicioController::class, 'store'])->name('store');
        Route::get('/{id}', [ServicioController::class, 'show'])->name('show');
        Route::patch('/{id}', [ServicioController::class, 'update'])->name('update');
        Route::delete('/{id}', [ServicioController::class, 'destroy'])->name('destroy');
    });
});

Route::patch('/users/{user}/tramitador', [UserController::class, 'updateTramitador'])
    ->name('users.tramitador.update')
    ->middleware('auth');

Route::post('/contratacion', [ContratacionController::class, 'store'])->name('contratacion.store');

Route::get('/operacion-exitosa', [StripeController::class, 'success'])
    ->name('operation.success')
    ->middleware('auth');

Route::middleware('auth')->group(function () {

    Route::get('ayuda-datos/{ayuda}/datos', [AyudaDatosController::class, 'datos'])
        ->name('ayuda_datos.datos');
    // Mostrar formulario
    Route::get('ayuda-datos/create', [AyudaDatosController::class, 'create'])
        ->name('ayuda_datos.create');

    // Procesar envío
    Route::post('ayuda-datos', [AyudaDatosController::class, 'store'])
        ->name('ayuda_datos.store');

    // Rutas para copiar ayuda_datos
    Route::post('ayuda-datos/copiar', [AyudaDatosController::class, 'copiarAyudaDatos'])
        ->name('ayuda_datos.copiar');
    Route::get('ayuda-datos/vista-previa', [AyudaDatosController::class, 'getDatosParaVistaPrevia'])
        ->name('ayuda_datos.vista_previa');
    Route::get('ayuda-datos/opciones-filtros', [AyudaDatosController::class, 'getOpcionesFiltros'])
        ->name('ayuda_datos.opciones_filtros');
    Route::get('ayuda-datos/datos-iniciales', [AyudaDatosController::class, 'getDatosIniciales'])
        ->name('ayuda_datos.datos_iniciales');
});

Route::get('/planes-productos', function () {
    return view('user.planes-productos', ['productos' => collect()]);
});

Route::get('/accept-invitation', function (Request $request) {
    // Lógica para asociar el usuario con la unidad familiar
    $ref_code = $request->cookie('ref_code');

    if ($ref_code) {
        $referrer = User::where('ref_code', $ref_code)->first();
        if ($referrer) {
            $user = auth()->user();
            $user->id_unidad_familiar = $referrer->id_unidad_familiar;
            $user->save();
        }
    }

    // Eliminar la cookie ref_code después de usarla
    Cookie::queue(Cookie::forget('ref_code'));

    // Redirigir al dashboard o a cualquier otra página
    return redirect()->route('user.home');
});
Route::post('/delete-ref-code-cookie', function () {
    // Eliminar la cookie ref_code
    cookie()->queue(cookie()->forget('ref_code'));

    // Retornar una respuesta de éxito
    return response()->json(['success' => true]);
})->name('delete.ref.code.cookie');

// Ruta CON id → para /planes-productos/ID
Route::get('/planes-productos/{id}', [AyudasShowController::class, 'showProducts'])->name('planes.productos');

Route::get('/family-members', [FamilyController::class, 'showMembers'])->name('user.family_members');

Route::post('/user/update-unidad-familiar', [FamilyController::class, 'updateUnidadFamiliar'])->name('user.updateUnidadFamiliar');

Route::get('/consultas', function () {
    return view('user.consultas');
})->middleware('auth')->name('user.consultas');

// ToDo: En el futuro esto habrá que permitirlo verlo SOLO si el usuario tiene ya esa ayuda pagada
Route::get('/RellenarAyuda/{id}', function ($id, \Illuminate\Http\Request $request) {
    $controller = new StripeController;
    $response = $controller->success($request);
    $userId = Auth::id();

    $ayuda = Ayuda::with('questionnaire.questions')->findOrFail($id);

    $mostrarPreguntas = $ayuda->questionnaire && $ayuda->questionnaire->tipo === QuestionnaireTipo::POST->value;

    $previousAnswers = DB::table('answers')
        ->where('user_id', $userId)
        ->pluck('answer', 'question_id');

    $questions = $ayuda->questionnaire->questions->map(function ($q) use ($previousAnswers) {
        return [
            'id' => $q->id,
            'text' => $q->text,
            'type' => $q->type,
            'answer' => $previousAnswers[$q->id] ?? null,
        ];
    });

    $documentosQuery = DB::table('ayuda_documentos')
        ->where('ayuda_id', $id)
        ->join('documents', 'ayuda_documentos.documento_id', '=', 'documents.id');

    // ➜ sólo documento id 3 si P1 == 2 y P3 == 1
    if (
        $ayuda->id !== 1 &&
        ($previousAnswers[1] ?? '') === 'Todavía no tengo contrato de alquiler firmado.' &&
        (int) ($previousAnswers[3] ?? 0) === 1
    ) {
        $documentosQuery->where('documents.id', 3);
    }
    $num_cuenta_valor = $previousAnswers[88] ?? null;
    $product_id_for_form = null;

    if (isset($ayuda->pago) && $ayuda->pago == 1) {
        $product = Product::where('ayudas_id', $ayuda->id)->first();

        if ($product) {
            $product_id_for_form = $product->id;
        } else {
            \Log::error("Error: No se encontró un producto para la ayuda con pago (ID: {$ayuda->id})");
        }
    }

    $documentos = $documentosQuery->select(
        'documents.id',
        'documents.name',
        'documents.description',
        'documents.allowed_types',
        'ayuda_documentos.es_obligatorio'
    )
        ->get();

    return view('user.rellenarayuda', [
        'ayuda' => $ayuda,
        'questions' => $questions,
        'documentos' => $documentos,
        'mostrarPreguntas' => $mostrarPreguntas,
        'num_cuenta' => $num_cuenta_valor,
        'product_id_for_form' => $product_id_for_form,
    ]);
})->middleware('auth')->name('user.rellenarayuda');
// Route::get('/RellenarAyuda/{id}', [RellenarAyudaController::class, 'show'])
//     ->middleware('auth')
//     ->name('user.rellenarayuda');

Route::post('/ayudas/{ayuda}/solicitar', [SolicitudAyudaController::class, 'store'])->name('ayuda.solicitar');

Route::post('/register-user', function (Request $request) {
    $user = Auth::user();

    if (! $user) {
        return response()->json(['error' => 'Usuario no autenticado'], 401);
    }

    $requestValue = function ($request, $key, $default = null) {
        return $request->input($key) ?? $request->input("answers.$key.value", $default);
    };

    function formatDate($fecha)
    {
        if (DateTime::createFromFormat('Y-m-d', $fecha) && DateTime::createFromFormat('Y-m-d', $fecha)->format('Y-m-d') === $fecha) {
            return $fecha;
        }

        if (DateTime::createFromFormat('d/m/Y', $fecha)) {
            return DateTime::createFromFormat('d/m/Y', $fecha)->format('Y-m-d');
        }

        return null;
    }

    $answersData = [
        'nombre_completo' => $requestValue($request, 'name', $requestValue($request, '24')),
        'dni_nie' => $requestValue($request, 'dni', $requestValue($request, '25')),
        'domicilio' => $requestValue($request, 'domicilio', $requestValue($request, '26')),
        'fecha_nacimiento' => formatDate($requestValue($request, 'fecha_nacimiento', $requestValue($request, '31'))),
        'estado_civil' => $requestValue($request, 'estado_civil', $requestValue($request, '32')),
        'sexo' => $requestValue($request, 'sexo', $requestValue($request, '33')),
        'dinero_ganado' => $requestValue($request, 'casilla435', $requestValue($request, '34')),
        'telefono' => $requestValue($request, 'telefono', $requestValue($request, '36')),
        'sin_deudas' => $requestValue($request, 'noDeudas', $requestValue($request, '35')),
        'esta_trabajando' => $requestValue($request, 'estaTrabajando', $requestValue($request, '37')),
        'provincia' => $requestValue($request, 'provincia', $requestValue($request, '27')),
        'municipio' => $requestValue($request, 'municipio', $requestValue($request, '28')),
        'comunidad_autonoma' => $requestValue($request, 'comunidadAutonoma', $requestValue($request, '29')),
        'nombre_completo' => $requestValue($request, 'name', $requestValue($request, '1')),
        'dni_nie' => $requestValue($request, 'dni', $requestValue($request, '2')),
        'domicilio' => $requestValue($request, 'domicilio', $requestValue($request, '3')),
        'fecha_nacimiento' => (
            $fecha = $requestValue($request, 'fecha_nacimiento', $requestValue($request, '8')))
            && DateTime::createFromFormat('Y-m-d', $fecha)
            && DateTime::createFromFormat('Y-m-d', $fecha)->format('Y-m-d') === $fecha
            ? $fecha
            : (DateTime::createFromFormat('d/m/Y', $fecha)
                ? DateTime::createFromFormat('d/m/Y', $fecha)->format('Y-m-d')
                : null
            ),
        'estado_civil' => $requestValue($request, 'estado_civil', $requestValue($request, '9')),
        'sexo' => $requestValue($request, 'sexo', $requestValue($request, '10')),
        'dinero_ganado' => $requestValue($request, 'casilla435', $requestValue($request, '11')),
        'telefono' => $requestValue($request, 'telefono', $requestValue($request, '13')),
        'tiene_deudas' => $requestValue($request, 'noDeudas', $requestValue($request, '12')),
        'esta_trabajando' => $requestValue($request, 'estaTrabajando', $requestValue($request, '14')),
        'provincia' => $requestValue($request, 'provincia', $requestValue($request, '4')),
        'municipio' => $requestValue($request, 'municipio', $requestValue($request, '5')),
        'comunidad_autonoma' => $requestValue($request, 'comunidadAutonoma', $requestValue($request, '6')),
        'is_demandante_empleo' => $requestValue($request, 'is_demandante_empleo', $requestValue($request, '47')),
        'tienePrestaciones' => $requestValue($request, 'tienePrestaciones', $requestValue($request, '48')),
        'tiene_hijos_o_pronto' => $request->has('hijos') ?? null,
    ];

    foreach ($answersData as $slug => $value) {
        $question = Question::where('slug', $slug)->first();
        if ($question) {
            Answer::create([
                'user_id' => $user->id,
                'question_id' => $question->id,
                'answer' => $value,
            ]);
        }
    }

    return redirect()->route('user.home');
});

Route::middleware('auth')->get('/Justificantes/{filename}', function ($filename) {
    preg_match('/-(\d{8}[A-Za-z])\.pdf$/', $filename, $matches);
    $dniFromFile = $matches[1] ?? null;

    $user = Auth::user();

    if ($user->is_admin || $dniFromFile && $user->taxInfo->nif === $dniFromFile) {
        $path = "/var/www/CollectorPuppeteer/Justificantes/{$filename}";

        if (File::exists($path)) {
            return Response::file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]);
        }

        abort(404);
    }

    abort(403, 'No autorizado');
});

Route::prefix('admin')->group(function () {
    Route::get('/users/{user}/details', function (User $user) {
        if (! Auth::check() || ! Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'user' => $user,
            'taxInfo' => $user->taxInfo,
        ]);
    })->name('admin.user.details');

    Route::delete('/users/{user}', function (User $user) {
        if (! Auth::check() || ! Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un administrador',
            ], 403);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'Usuario eliminado correctamente']);
    })->name('admin.user.destroy');
});

Route::get('/showToken', function () {
    return csrf_token();
});

// Ruta para verificar el email
Route::get('/verify-email/{id}/{token}', function ($id, $token) {
    $user = User::findOrFail($id);

    $expectedToken = hash('sha256', $user->email.$user->created_at.config('app.key'));

    // Elegir ruta de destino según si el usuario está autenticado o no
    $targetRoute = Auth::check() ? 'user.home' : 'login';

    if ($token !== $expectedToken) {
        return redirect()->route($targetRoute)
            ->with('error', 'El enlace de verificación no es válido o ha expirado.');
    }

    // Si el email ya está verificado, redirigir con mensaje
    if ($user->email_verified_at) {
        return redirect()->route($targetRoute)
            ->with('success', 'Tu correo electrónico ya está verificado.');
    }

    // Verificar el email
    $user->email_verified_at = now();
    $user->save();

    return redirect()->route($targetRoute)
        ->with('success', '¡Correo electrónico verificado correctamente! Ya puedes iniciar sesión.');
})->name('verify.email');

Route::post('/register-email', function (Request $request) {
    $request->validate([
        'name' => 'required',
        'password' => 'required|min:8',
    ], [
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
    ]);

    $input = $request->name;
    $fieldType = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
    $user = User::where($fieldType, $input)->first();

    if ($user) {
        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'La contraseña es incorrecta']);
        }

        Auth::login($user);

        if (Cookie::has('ref_code') && empty($user->ref_by)) {
            $referrer = User::where('ref_code', Cookie::get('ref_code'))->first();

            // Asegúrate de que el usuario no se auto-referencie
            if ($referrer && $referrer->id !== $user->id) {
                $user->ref_by = $referrer->id;
                $user->save();
            }
        }

        return redirect()->intended(route('admin.dashboardv2'));
    } else {
        $errorField = $fieldType === 'email' ? 'email' : 'name';

        return back()->withErrors([$errorField => 'No existe ninguna cuenta con esos datos.'])->with('redirect_to_register', true);
    }
})->name('register.email');

Route::post('/register-account', function (Request $request) {
    // 1) Validaciones
    Log::info('Registering account with data: ', $request->all());

    $request->validate([
        'email' => 'required|email|max:255',
        'password' => 'required|min:8',
        'password2' => 'required|same:password',
    ], [
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password2.same' => 'Las contraseñas no coinciden.',
    ]);

    // 2) Duplicados
    if (User::where('email', $request->email)->exists()) {
        return back()
            ->withErrors(['email' => 'Este correo ya está registrado. Intenta iniciar sesión.'])
            ->with('redirect_to_login', true);
    }

    // 3) Referido
    $referrerId = null;
    if (Cookie::has('ref_code')) {
        $referrerId = optional(User::where('ref_code', Cookie::get('ref_code'))->first())->id;
    }

    // 4) Crear usuario local (sin verificar email)
    $user = User::create([
        'name' => $request->email, // aún no tenemos nombre real
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'email_verified_at' => null,
        'ref_by' => $referrerId,
    ]);
    // Lanza el evento de registro de usuario
    event(new EventUserRegistered($user, ['Hubspot']));

    // 8) CRM (histórico estado + embudo)
    CrmStateHistory::create([
        'user_id' => $user->id,
        'ayuda_id' => null,
        'from_temp' => 'null',
        'to_temp' => 'frio',
        'from_stage' => 'null',
        'to_stage' => 'Captado',
        'event' => 'user_registered',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    UserAyuda::create([
        'user_id' => $user->id,
        'ayuda_id' => null,
        'tags' => null,
        'estado_comercial' => 'frio',
    ]);

    // 10) Login y redirección
    Auth::login($user);

    return redirect()->route('registercollector')
        ->with('success', 'Cuenta creada correctamente. Inicia sesión para continuar.');
})->name('register.account');

Route::get('/auth/google/callback', [SocialLoginController::class, 'handleGoogleCallback']);
Route::get('/login/google', [SocialLoginController::class, 'redirectToGoogle'])->name('login.google');
Route::middleware('auth')->group(function () {
    Route::get('/setup-card', [StripeSetupController::class, 'showForm']);
    Route::post('/setup-card', [StripeSetupController::class, 'store'])->name('setup-card');
});

Route::post('/storeQuestionnaireDraft', [FormController::class, 'storeDraft'])->name('storeQuestionnaireDraft');

Route::post('/stripewebhook', [StripeSetupController::class, 'handle']);

// Webhooks de HubSpot (sin autenticación, validación por firma)
Route::post('/webhooks/hubspot', [\App\Http\Controllers\HubspotWebhookController::class, 'handle'])
    ->name('webhooks.hubspot');
Route::get('/webhooks/hubspot/health', [\App\Http\Controllers\HubspotWebhookController::class, 'health'])
    ->name('webhooks.hubspot.health');
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/cobros', [AdminCobrosController::class, 'index'])->name('admin.cobros');
    Route::post('/admin/cobros', [AdminCobrosController::class, 'chargeSelectedUser']);
});
Route::middleware('auth')->group(function () {
    Route::get('/cambiar-tarjeta', [StripeSetupController::class, 'editPaymentMethod'])->name('editPaymentMethod');
    Route::post('/update-payment-method', [StripeSetupController::class, 'updatePaymentMethod'])->name('updatePaymentMethod');
});

// Rutas para el apartado "Editar Perfil" solo si está autenticado
Route::middleware('auth')->get('/users-info', [UserInfoController::class, 'index']);
Route::middleware('auth')->get('/profile-update', [UserInfoController::class, 'editProfile'])->name('user.profile-update');
Route::middleware('auth')->post('/profile-update', [UserInfoController::class, 'updateProfile'])->name('user.profile.update'); // Nueva ruta para POST
// Rutas para el apartado "Historial de Ayudas" solo si está autenticado
Route::middleware('auth')->group(function () {
    Route::get('/historial-ayudas', [HistorialAyudasController::class, 'index'])->name('user.historial-ayudas');
});

Route::middleware('auth')->group(function () {
    Route::get('/historial-pagos', [HistorialPagosController::class, 'index'])->name('user.historial-pagos');
});
// Rutas para restablecer la contraseña
Route::post('password/email', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset', [PasswordResetController::class, 'showRequestForm'])->name('password.request');
Route::get('/passwords/reset/{user}/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password-update', [PasswordResetController::class, 'resetPassword'])->name('auth.passwords.password-update');
Route::get('/recuperar', function () {
    return view('user.recuperar');
});

Route::get('/error', function () {
    return view('user.error');
})->name('user.error');

// Ruta para verificar autenticación desde páginas de error
Route::get('/api/check-auth', function () {
    return response()->json([
        'authenticated' => auth()->check(),
        'user' => auth()->user() ? [
            'id' => auth()->user()->id,
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
        ] : null,
    ]);
});

// Ruta para crear tickets desde páginas de error
Route::post('/tickets', [TicketController::class, 'store'])->middleware('auth')->name('tickets.store');

// Ruta alternativa para tickets sin CSRF (para páginas de error)
Route::post('/tickets/create', function (\Illuminate\Http\Request $request) {
    // Log para debug
    \Illuminate\Support\Facades\Log::info('Datos recibidos en ticket:', $request->all());

    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
        'url_error' => 'required|string|max:500',
        'navegador' => 'required|string|max:100',
        'version_navegador' => 'nullable|string|max:50',
        'so' => 'required|string|max:100',
        'descripcion' => 'nullable|string|max:1000',
    ]);

    if ($validator->fails()) {
        \Illuminate\Support\Facades\Log::error('Validación falló:', $validator->errors()->toArray());

        return response()->json([
            'success' => false,
            'message' => 'Datos inválidos',
            'errors' => $validator->errors(),
        ], 422);
    }

    try {
        $ticket = \App\Models\Ticket::create([
            'user_id' => auth()->id(),
            'url_error' => $request->url_error,
            'navegador' => $request->navegador,
            'version_navegador' => $request->version_navegador,
            'so' => $request->so,
            'descripcion' => $request->descripcion,
            'estado' => \App\Models\Ticket::ESTADO_PENDIENTE,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket creado correctamente',
            'ticket_id' => $ticket->id,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al crear el ticket',
            'error' => $e->getMessage(),
        ], 500);
    }
})->middleware('auth')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/dashboard/historial', function () {
    $drafts = QuestionnaireDraft::orderBy('questionnaire_id')
        ->orderBy('session_token')
        ->orderBy('time_start')
        ->get();

    $agrupados = $drafts
        ->groupBy('questionnaire_id')
        ->map(function ($draftsFormulario, $formularioId) {
            $sesiones = $draftsFormulario
                ->groupBy('session_token')
                ->map(function ($sesion) {
                    $user = User::find($sesion->first()->user_id);

                    $totalSeconds = $sesion->sum(function ($item) {
                        return $item->time_start && $item->time_end
                            ? Carbon::parse($item->time_start)->diffInSeconds(Carbon::parse($item->time_end))
                            : 0;
                    });

                    return [
                        'user_id' => $sesion->first()->user_id,
                        'user_name' => $user?->name ?? 'N/D',
                        'time_end' => $sesion->first()->time_end,
                        'session_id' => $sesion->first()->session_id,
                        'session_token' => $sesion->first()->session_token,
                        'total_interactions' => $sesion->count(),
                        'total_minutes' => gmdate('i:s', $totalSeconds),
                        'last_time' => $sesion->max('time_end'),
                        'acciones' => $sesion->map(fn ($d) => [
                            'direction' => $d->direction,
                            'respuesta' => $d->respuesta,
                        ])->toArray(),
                    ];
                })
                ->sortByDesc('last_time')
                ->values();

            // PAGINACIÓN DE SESIONES por formulario
            $perPage = 5;
            $currentPage = request()->get("page_{$formularioId}", 1);
            $paginatedSesiones = new LengthAwarePaginator(
                $sesiones->forPage($currentPage, $perPage),
                $sesiones->count(),
                $perPage,
                $currentPage,
                [
                    'pageName' => "page_{$formularioId}",
                    'path' => url()->current(), // <- esto fuerza la URL correcta
                ]
            );
            $formulario = Questionnaire::find($formularioId);

            return [
                'formulario_id' => $formularioId,
                'formulario_name' => $formulario?->name ?? "Formulario ID: $formularioId",
                'sesiones' => $paginatedSesiones,
            ];
        });

    // Estadísticas adicionales
    $agrupadoPorToken = $drafts->filter(fn ($d) => $d->session_token !== null)
        ->groupBy('session_token');

    $cuestionariosIncompletos = $agrupadoPorToken->filter(
        fn ($g) => $g && count($g) === 1
    )->count();
    $usuariosUnicos = QuestionnaireDraft::distinct('user_id')->count('user_id');

    $duraciones = [];
    foreach ($agrupadoPorToken as $g) {
        $inicio = $g->min('time_start');
        $fin = $g->max('time_end') ?? $inicio;
        if ($inicio && $fin && $inicio != $fin) {
            $duraciones[] = Carbon::parse($inicio)->diffInSeconds(Carbon::parse($fin));
        }
    }

    $tiempoPromedio = count($duraciones)
        ? gmdate('i:s', array_sum($duraciones) / count($duraciones))
        : '00:00';

    return view('admin.historialquestionnaire', [
        'formularios' => $agrupados,
        'totalSesiones' => $agrupadoPorToken->count(),
        'registrosUnicos' => $cuestionariosIncompletos,
        'usuariosUnicos' => $usuariosUnicos,
        'tiempoPromedio' => $tiempoPromedio,
    ]);
})->middleware('auth')->name('admin.historialquestionnaire');

Route::get('/rellenar-form/{id}', [FormController::class, 'show'])
    ->middleware('auth')
    ->name('user.form-specific');

Route::post('/checkAnswers', [FormController::class, 'store'])->name('checkAnswers');

Route::post('/checkout', [StripeController::class, 'createCheckoutSession']);

Route::get('/planes-productos', function () {
    return view('planes-productos');
})->name('planes-productos');

Route::get('/nueva-cuenta', [RegisterReferralController::class, 'showRegistrationForm'])->name('register.new');

// Ruta para la vista beneficiario
Route::get('/beneficiario', function (Request $request) {
    $ayuda_id = $request->query('ayuda_id');

    if (! $ayuda_id) {
        return view('user.home');
    }

    $ayuda = Ayuda::findOrFail($ayuda_id);
    $currentUserId = SimulationHelper::getCurrentUserId();
    $currentUser = SimulationHelper::getCurrentUser();

    if ($ayuda->pago && ! $ayuda->questionnaire_id) {
        AyudaSolicitada::updateOrCreate(
            ['user_id' => $currentUserId, 'ayuda_id' => $ayuda_id],
            [
                'estado' => 'Pendiente de tramitar',
                'fecha_solicitud' => now(),
            ]
        );
    }

    $ayudaSolicitada = AyudaSolicitada::where('user_id', $currentUserId)
        ->where('ayuda_id', $ayuda_id)
        ->first();

    if (! $ayudaSolicitada) {
        return redirect()->route('user.home')->with('error', 'No tienes acceso a esta ayuda.');
    }

    $ayuda = Ayuda::with('organo')->findOrFail($ayuda_id);

    return view('user.beneficiario', [
        'nombre_ayuda' => $ayuda->nombre_ayuda,
        'cuantia_usuario' => $ayuda->cuantia_usuario,
        'fecha_fin' => $ayuda->fecha_fin,
        'organo_name' => $ayuda->organo->nombre_organismo ?? null,
        'ayuda' => $ayuda,
    ]);
})->middleware('auth')->name('user.beneficiario');

// Ruta para beneficiario no tramitable
Route::get('/beneficiario-no-tramitable', function () {
    return view('user.beneficiario-no-tramitable');
})->middleware('auth')->name('user.beneficiario-no-tramitable');

// Ruta para la vista no beneficiario buena para que los usuarios no puedan entra por URL,
// esta para cuando terminamos de probar si funciona o no
Route::get('/no-beneficiario', function (Request $request) {
    $currentUser = Auth::user();
    $ayudaId = session('ayuda_id');
    $motivo = session('motivo');
    $ayuda = Ayuda::find($ayudaId);

    return view('user.no-beneficiario', compact('motivo', 'ayuda'));
})->middleware('auth')->name('user.no-beneficiario');

Route::get('/municipios/{provinciaId}', [MunicipioController::class, 'getByProvincia']);
Route::get('/api/provincias', function () {
    $provincias = Provincia::orderBy('nombre_provincia')
        ->pluck('nombre_provincia', 'id')
        ->toArray();

    return response()->json($provincias);
});

Route::get('/admin/searchCCAA', function () {
    $ccaa = Ccaa::orderBy('nombre_ccaa')->pluck('nombre_ccaa')->toArray();

    return response()->json($ccaa);
});

Route::get('/admin/searchProvincias', function (Request $request) {
    $query = Provincia::orderBy('nombre_provincia');

    if ($request->has('ccaa')) {
        $ccaa = Ccaa::where('nombre_ccaa', $request->ccaa)->first();
        if ($ccaa) {
            $query->where('id_ccaa', $ccaa->id);
        }
    }

    $provincias = $query->pluck('nombre_provincia')->toArray();

    return response()->json($provincias);
});

Route::get('/admin/searchMunicipios', function (Request $request) {
    $query = Municipio::orderBy('nombre_municipio');

    if ($request->has('provincia')) {
        $provincia = Provincia::where('nombre_provincia', $request->provincia)->first();
        if ($provincia) {
            $query->where('provincia_id', $provincia->id);
        }
    }

    $municipios = $query->pluck('nombre_municipio')->toArray();

    return response()->json($municipios);
});

Route::post(
    '/contrataciones/{id}/upload-missing-document',
    [DocumentoController::class, 'uploadMissingDocument']
)->name('documentos.upload-missing');

Route::post(
    '/contrataciones/{id}/upload-documento-tramitacion',
    [DocumentoController::class, 'uploadDocumentoTramitacion']
)->middleware('auth')->name('documentos.upload-tramitacion');

Route::post(
    '/contrataciones/{id}/convivientes',
    [ContratacionController::class, 'addConviviente']
)->name('convivientes.add');

Route::delete(
    '/contrataciones/{id}/convivientes/{conviviente}',
    [ContratacionController::class, 'removeConviviente']
)->name('convivientes.remove');

Route::post('/contrataciones/{id}/arrendadores', [ContratacionController::class, 'addArrendador'])->middleware('auth');
Route::delete('/contrataciones/{contratacion}/arrendadores/{arrendador}', [ContratacionController::class, 'removeArrendador'])->middleware('auth');

// Rutas para documentos de tramitación
Route::get('/contrataciones/documentos-internos', [ContratacionController::class, 'getDocumentosInternos'])->middleware('auth');
Route::post('/contrataciones/documentos-internos', [ContratacionController::class, 'createDocumentoInterno'])->middleware('auth');
Route::post('/contrataciones/{id}/documentos-tramitacion', [ContratacionController::class, 'addDocumentoTramitacion'])->middleware('auth');
Route::delete('/contrataciones/{contratacion}/documentos-tramitacion/{documento}', [ContratacionController::class, 'removeDocumentoTramitacion'])->middleware('auth');

// Rutas para el panel de trámites de Operativa
Route::get('/tramites', [AdminBusquedaController::class, 'index'])->name('admin.tramites');
Route::put('/admin/estado/{id}/update', [AdminEstadoController::class, 'update'])->name('admin.estado.update');
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::post('/estado/{id}/update', [AdminBusquedaController::class, 'update'])->name('admin.estado.update');
});

// DESACOPLADO: panel de usuario
// Route::get('/usuarios/{user}', [AdminUserPanelController::class, 'show'])->name('admin.panel-usuario');
// Route::get('/admin/usuario/{user}/editar', [AdminUserPanelController::class, 'editarUsuario'])
//     ->name('admin.editar-usuario');
// Route::post('/admin/usuario/{user}/actualizar', [AdminUserPanelController::class, 'actualizarRespuestas'])
//     ->name('admin.actualizar-respuestas');

Route::prefix('admin')->middleware(['auth'])->group(function () {
    // Mostrar formulario para ver y editar el estado
    Route::get('/estado/{id}', [AdminEstadoController::class, 'show'])->name('admin.estado.show');

    // Actualizar el estado (envía el correo tras actualizar)
    Route::post('/estado/{id}', [AdminEstadoController::class, 'update'])->name('admin.estado.update');
    Route::get('/simulation', [UserSimulationController::class, 'index'])->name('admin.simulation.index');
    Route::get('/simulation/search', [UserSimulationController::class, 'search'])->name('admin.simulation.search');
    Route::get('/simulation/status', [UserSimulationController::class, 'status'])->name('admin.simulation.status');
    Route::get('/simulation/start/{userId}', [UserSimulationController::class, 'startSimulation'])->name('admin.simulation.start');
    Route::get('/simulation/stop', [UserSimulationController::class, 'stopSimulation'])->name('admin.simulation.stop');
});

/*
Pertenece a la antigua vista para editar usuarios

Route::get('/datos-usuario/{user}/{contratacion}', [AdminBusquedaController::class, 'verDatosUsuario'])->name('admin.datos-usuario');
Route::post('/admin/respuestas/{user}/{contratacion}', [AdminRespuestaController::class, 'actualizar'])->name('admin.actualizar-respuestas');
Route::post('/admin/datos-usuario/{id}/preguntas', [AdminDatosUsuarioController::class, 'store'])->name('admin.datos-usuario.preguntas.store');*/

Route::post('/subir-documento', [App\Http\Controllers\UserDocumentController::class, 'store'])->name('documentos.subir');

Route::get('/documentacion-usuario/{id}', [DocumentoController::class, 'index'])->middleware('auth')->name('admin.documentacion-usuario');
Route::get('/api/documentos', [DocumentoController::class, 'getAllDocuments'])->middleware('auth')->name('api.documentos.all');
Route::get('/onboarding', action: [AyudasPosiblesController::class, 'indexOnboarding'])->middleware(['auth', UserLoggedAndInitialFormDone::class])->name('user.onboarding');
Route::post('/solicitar-revision', [AyudasSolicitadasController::class, 'solicitarRevision'])->name('user.solicitar-revision');

Route::post('/solicitud', [App\Http\Controllers\AyudasSolicitadasController::class, 'storeSolicitud'])
    ->name('solicitud.store');

Route::post('/solicitud/ajax', [App\Http\Controllers\AyudasSolicitadasController::class, 'storeSolicitudAjax'])
    ->name('solicitud.store.ajax')
    ->middleware('auth');

// Route::post('/solicitud', [AyudasSolicitadasController::class, 'storeSolicitud'])->name('solicitud.store');

// Ruta AJAX para obtener una ayuda solicitada individualmente
Route::get('/ayudas-solicitadas/{id}', [App\Http\Controllers\AyudasSolicitadasController::class, 'show'])->middleware('auth')->name('user.AyudasSolicitadas.show');

Route::middleware('auth')->get('/recursos', [App\Http\Controllers\UserController::class, 'hasContrataciones'])->name('user.recursos');
Route::middleware('auth')->get('/recursos/{contratacion_id}', [App\Http\Controllers\UserController::class, 'ayudaDetalle'])->name('user.ayuda-recurso');
Route::put('/admin/ayudas/recursos/{id}', [App\Http\Controllers\AyudaRecursoController::class, 'update'])->name('ayudas.recursos.update');
Route::delete('/admin/ayudas/recursos/{id}/desasociar', [AyudaRecursoController::class, 'desasociar'])->name('ayudas.recursos.desasociar');
Route::delete('/admin/ayudas/recursos/{id}/eliminar', [AyudaRecursoController::class, 'eliminar'])->name('ayudas.recursos.eliminar');

Route::get('/invite', function (Request $request) {
    $ref = $request->query('ref');

    return redirect()->route('register.new', ['ref_code' => $ref]);
});

Route::post('/conviviente/generar-enlace', [ConvivienteFormularioController::class, 'generarEnlace'])->name('conviviente.generar.enlace');
Route::get('/conviviente/form/{token}', [ConvivienteFormularioController::class, 'showPublicConviviente'])->name('conviviente.public.form');

Route::prefix('admin')->middleware(['auth'])->group(function () {
    // Rutas para gestión de flujos de transiciones
    Route::get('/flujos-tramitacion', [App\Http\Controllers\Admin\FlujoController::class, 'index'])->name('admin.flujos-tramitacion');
    Route::get('/flujos/por-ayuda', [App\Http\Controllers\Admin\FlujoController::class, 'getFlujosPorAyuda'])->name('admin.flujos.por-ayuda');
    Route::post('/flujos', [App\Http\Controllers\Admin\FlujoController::class, 'store'])->name('admin.flujos.store');
    Route::put('/flujos/{flujo}', [App\Http\Controllers\Admin\FlujoController::class, 'update'])->name('admin.flujos.update');
    Route::delete('/flujos/{flujo}', [App\Http\Controllers\Admin\FlujoController::class, 'destroy'])->name('admin.flujos.destroy');
    Route::get('/flujos/estados-disponibles', [App\Http\Controllers\Admin\FlujoController::class, 'getEstadosDisponibles'])->name('admin.flujos.estados-disponibles');
    Route::get('/flujos/fases-disponibles', [App\Http\Controllers\Admin\FlujoController::class, 'getFasesDisponibles'])->name('admin.flujos.fases-disponibles');

    // Rutas para copiar flujos
    Route::post('/flujos/copiar', [App\Http\Controllers\Admin\FlujoController::class, 'copiarFlujos'])->name('admin.flujos.copiar');
    Route::get('/flujos/ayudas-para-copiar', [App\Http\Controllers\Admin\FlujoController::class, 'getAyudasParaCopiar'])->name('admin.flujos.ayudas-para-copiar');
    Route::get('/flujos/vista-previa', [App\Http\Controllers\Admin\FlujoController::class, 'getFlujosParaVistaPrevia'])->name('admin.flujos.vista-previa');

    Route::get('/logicas', [AyudasRequisitosJsonController::class, 'index'])->name('admin.logicas');
    Route::get('/logicas/{id}', [AyudasRequisitosJsonController::class, 'ayuda'])->name('admin.logicas.ayuda');
    Route::post('/ayudas/{ayuda}/requisitos-json', [AyudasRequisitosJsonController::class, 'storeJson'])->name('admin.ayudas.requisitosjson.store');
    Route::put('/ayudas/requisitos-json/{id}', [AyudasRequisitosJsonController::class, 'updateJson'])->name('admin.ayudas.requisitosjson.update');
    Route::put('/ayudas/{ayuda}/requisitos-json/bulk', [AyudasRequisitosJsonController::class, 'bulkUpdate'])->name('admin.ayudas.requisitosjson.bulk');
    Route::put('/ayudas/{ayuda}/requisitos-json', [AyudasRequisitosJsonController::class, 'updateAllJson'])->name('admin.ayudas.requisitosjson.updateall');
    // DESACOPLADO: panel usuario - comunicaciones y partial
    // Route::post('/users/{user}/comunicaciones', [AdminUserPanelController::class, 'storeComunicacion'])->name('admin.users.comunicaciones.store');
    // Route::delete('/users/{user}/comunicaciones/{comunicacion}', [AdminUserPanelController::class, 'deleteComunicacion'])->name('admin.users.comunicaciones.destroy');
    // Route::post('/users/{user}/comunicacion-operativa', [AdminUserPanelController::class, 'registrarComunicacionOperativa'])->name('admin.users.comunicacion_operativa');
    // Route::get('/panel-usuario/{user}/partial', [AdminUserPanelController::class, 'showPartial']);
    Route::delete('/ayudas/requisitos-json/{id}', [AyudasRequisitosJsonController::class, 'destroy'])->name('admin.ayudas.requisitosjson.destroy');
    Route::get('/ayudas/{ayuda}/condiciones-cuestionario', [QuestionConditionController::class, 'condicionesCuestionario']);
    Route::get('/questionnaires/{questionnaire}/questions', [QuestionConditionController::class, 'index']);
    Route::post('/questionnaires/{questionnaire}/questions', [QuestionConditionController::class, 'storeQuestion']);
    Route::put('/questions/{question}', [QuestionConditionController::class, 'updateQuestion']);
    Route::delete('/questions/{question}', [QuestionConditionController::class, 'destroyQuestion']);
    Route::get('/questionnaires/{questionnaire}/conditions', [QuestionConditionController::class, 'indexConditions']);
    Route::post('/questionnaires/{questionnaire}/conditions', [QuestionConditionController::class, 'storeCondition']);
    Route::post('/questionnaires/{questionnaire}/conditions/batch', [QuestionConditionController::class, 'storeBatchConditions']);
    Route::post('/questionnaires/{questionnaire}/conditions/create', [QuestionConditionController::class, 'createConditionsFromDraft']);
    Route::put('/conditions/{condition}', [QuestionConditionController::class, 'updateCondition']);
    Route::delete('/conditions/{condition}', [QuestionConditionController::class, 'destroyCondition']);
    Route::delete('/questionnaires/{questionnaire}/conditions/all', [QuestionConditionController::class, 'destroyAllConditions']);
    Route::get('/ayudas/{ayuda}/questionnaire', [AyudasRequisitosJsonController::class, 'getQuestionnaireByAyuda']);
    Route::get('/ayudas/{ayuda}/requisitos', [AyudasRequisitosJsonController::class, 'getRequisitos']);
    Route::get('/ayudas/{ayuda}/versions/requisitos', [App\Http\Controllers\Admin\VersionController::class, 'getRequisitosVersions']);
    Route::get('/questionnaires/{questionnaire}/versions/conditions', [App\Http\Controllers\Admin\VersionController::class, 'getConditionsVersions']);
    Route::post('/ayudas/{ayuda}/versions/requisitos/draft', [App\Http\Controllers\Admin\VersionController::class, 'createRequisitosDraft']);
    Route::post('/questionnaires/{questionnaire}/versions/conditions/draft', [App\Http\Controllers\Admin\VersionController::class, 'createConditionsDraft']);
    Route::post('/versions/requisitos/{version}/publish', [App\Http\Controllers\Admin\VersionController::class, 'publishRequisitosVersion']);
    Route::post('/versions/conditions/{version}/publish', [App\Http\Controllers\Admin\VersionController::class, 'publishConditionsVersion']);
    Route::put('/versions/requisitos/{version}/draft', [App\Http\Controllers\Admin\VersionController::class, 'updateRequisitosDraft']);
    Route::put('/versions/conditions/{version}/draft', [App\Http\Controllers\Admin\VersionController::class, 'updateConditionsDraft']);
    Route::delete('/versions/{type}/{version}', [App\Http\Controllers\Admin\VersionController::class, 'deleteVersion']);
    Route::post('/ayudas/{ayuda}/versions/requisitos/{version}/edit', [App\Http\Controllers\Admin\VersionController::class, 'editRequisitosVersion']);
    Route::post('/questionnaires/{questionnaire}/versions/conditions/{version}/edit', [App\Http\Controllers\Admin\VersionController::class, 'editConditionsVersion']);
    Route::get('/questions', [QuestionController::class, 'index']);
    Route::get('/questions/search', [QuestionController::class, 'search']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/search', [UserController::class, 'search']);

    Route::get('/admin-panel/users/search', [App\Http\Controllers\AdminUserPanelController::class, 'searchUsers'])->name('admin.users.search');
    Route::get('/admin-panel/users/test', [App\Http\Controllers\AdminUserPanelController::class, 'testSearch'])->name('admin.users.test');
    Route::get('/users/{user}/answers', [UserController::class, 'showAnswers']);
    Route::post('/ayudas/{ayuda}/test-requirements', [AyudasRequisitosJsonController::class, 'testRequirements']);
    Route::post('/ayudas/{ayuda}/test-user-requirements', [AyudasRequisitosJsonController::class, 'testUserRequirements']);
    Route::get('/ayudas/{ayuda}/questionnaire-data', [AyudasRequisitosJsonController::class, 'getQuestionnaireData']);
    Route::post('/ayudas/{ayuda}/validate-flow', [AyudasRequisitosJsonController::class, 'validateFlow']);
    Route::post('/ayudas/{ayuda}/eligibility-requirements', [AyudasRequisitosJsonController::class, 'saveEligibilityRequirements']);
    Route::get('/questions/all', [AyudasRequisitosJsonController::class, 'getAllQuestions']);
    Route::get('/documentos', [DocumentoController::class, 'allDocuments'])->name('admin.documentos');
    Route::post('/documentos/search', [DocumentoController::class, 'searchDocuments'])->name('admin.documentos.search');
    Route::get('/tickets', [TicketController::class, 'index'])->name('admin.tickets.index');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('admin.tickets.show');
    Route::put('/tickets/{ticket}/estado', [TicketController::class, 'updateEstado'])->name('admin.tickets.update-estado');
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('admin.tickets.destroy');
    Route::get('/tickets/estadisticas', [TicketController::class, 'estadisticas'])->name('admin.tickets.estadisticas');

    // Rutas para wizards
    Route::get('/wizards', [WizardController::class, 'index'])->name('wizards.index');
    Route::get('/wizards/create', [WizardController::class, 'create'])->name('wizards.create');
    Route::post('/wizards', [WizardController::class, 'store'])->name('wizards.store');
    Route::get('/wizards/{wizard}', [WizardController::class, 'show'])->name('wizards.show');
    Route::put('/wizards/{wizard}', [WizardController::class, 'update'])->name('wizards.update');
    Route::post('/wizards/{wizard}/draft', [WizardController::class, 'saveDraft'])->name('wizards.save-draft');
    Route::post('/wizards/{wizard}/complete', [WizardController::class, 'complete'])->name('wizards.complete');
    Route::delete('/wizards/{wizard}', [WizardController::class, 'destroy'])->name('wizards.destroy');
    Route::post('/wizards/{wizard}/duplicate', [WizardController::class, 'duplicate'])->name('wizards.duplicate');
    Route::get('/wizards/{wizard}/export', [WizardController::class, 'export'])->name('wizards.export');
    Route::post('/wizards/import', [WizardController::class, 'import'])->name('wizards.import');
    Route::get('/wizards/data-structure', [WizardController::class, 'getDataStructure'])->name('wizards.data-structure');
    Route::get('/wizards/form-data', [WizardController::class, 'getFormData'])->name('wizards.form-data');

    // Rutas para gestión de preguntas en wizards
    Route::get('/wizards/questions/search', [WizardController::class, 'searchQuestions'])->name('wizards.questions.search');
    Route::post('/wizards/questions', [WizardController::class, 'createQuestion'])->name('wizards.questions.create');

    // Rutas para wizard de mail
    Route::post('/wizards/preview-users', [WizardController::class, 'previewUsers'])->name('wizards.preview-users');
    Route::patch('/users/{user}/answers/{answerId}', [AdminUserPanelController::class, 'updateAnswer'])->name('admin.users.answers.update');
    Route::get('/options', [AdminUserPanelController::class, 'getOptions'])->name('admin.options');

    Route::delete('/users/{user}/answers/{answerId}', [AdminUserPanelController::class, 'deleteAnswer'])->name('admin.users.answers.delete');
    Route::delete('/users/{user}/solicitudes/{solicitud}', [AyudasSolicitadasController::class, 'destroySolicitud'])->name('admin.users.solicitudes.destroy');
    Route::delete('/users/{user}/delete', [UserController::class, 'deleteUser'])->name('admin.users.delete');

    // Rutas para alertas de ventas
    Route::get('/users/{user}/sale-alerts', [\App\Http\Controllers\SaleAlertController::class, 'getByUser'])->name('admin.users.sale-alerts');
    Route::post('/sale-alerts', [\App\Http\Controllers\SaleAlertController::class, 'store'])->name('admin.sale-alerts.store');
    Route::patch('/sale-alerts/{saleAlert}', [\App\Http\Controllers\SaleAlertController::class, 'update'])->name('admin.sale-alerts.update');
    Route::delete('/sale-alerts/{saleAlert}', [\App\Http\Controllers\SaleAlertController::class, 'destroy'])->name('admin.sale-alerts.destroy');
    Route::patch('/sale-alerts/{saleAlert}/complete', [\App\Http\Controllers\SaleAlertController::class, 'markAsCompleted'])->name('admin.sale-alerts.complete');
    Route::get('/sale-alerts', [\App\Http\Controllers\SaleAlertController::class, 'index'])->name('admin.sale-alerts.index');
    Route::get('/sale-alerts/proximas', [\App\Http\Controllers\SaleAlertController::class, 'getProximas'])->name('admin.sale-alerts.proximas');
    Route::get('/sale-alerts/vencidas', [\App\Http\Controllers\SaleAlertController::class, 'getVencidas'])->name('admin.sale-alerts.vencidas');
    Route::get('/sale-alerts/stats', [\App\Http\Controllers\SaleAlertController::class, 'getStats'])->name('admin.sale-alerts.stats');

    Route::get('/gestion-ayudas', [GestionAyudasController::class, 'index'])->name('admin.gestion-ayudas.index');
    Route::get('/question-categories', [QuestionCategoryController::class, 'index'])->name('admin.question-categories.index');
    Route::post('/question-categories', [QuestionCategoryController::class, 'store'])->name('admin.question-categories.store');
    Route::put('/question-categories/{questionCategory}', [QuestionCategoryController::class, 'update'])->name('admin.question-categories.update');
    Route::delete('/question-categories/{questionCategory}', [QuestionCategoryController::class, 'destroy'])->name('admin.question-categories.destroy');
    Route::get('/question-purposes', [QuestionPurposeController::class, 'index'])->name('admin.question-purposes.index');
    Route::post('/question-purposes', [QuestionPurposeController::class, 'store'])->name('admin.question-purposes.store');
    Route::put('/question-purposes/{questionPurpose}', [QuestionPurposeController::class, 'update'])->name('admin.question-purposes.update');
    Route::delete('/question-purposes/{questionPurpose}', [QuestionPurposeController::class, 'destroy'])->name('admin.question-purposes.destroy');
    Route::get('/questions', [QuestionController::class, 'index'])->name('admin.questions.index');
    Route::get('/questions/list', [QuestionController::class, 'index'])->name('admin.questions.list');
    Route::post('/questions', [QuestionController::class, 'store'])->name('admin.questions.store');
    Route::patch('/questions/{id}', [QuestionController::class, 'update'])->name('admin.questions.update');
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy'])->name('admin.questions.destroy');
    Route::get('/questions/search', [QuestionController::class, 'search'])->name('admin.questions.search');
    Route::get('/preguntas', [QuestionController::class, 'index'])->name('admin.preguntas');

    Route::get('/onboarders/get-or-create', [OnboarderController::class, 'getOrCreate'])->name('admin.onboarders.get-or-create');
});

// Subsanacion docs
Route::post('/subsanacion/{subsanacionDocId}/upload', [UserDocumentController::class, 'subirDocumentoSubsanacion'])->name('subsanacion.upload');

Route::post('/subsanacion/{contratacionId}/marcar', [UserDocumentController::class, 'marcarDocumentosSubsanacion'])->name('subsanacion.marcar');

Route::get('/ayudas-solicitadas/{id}/subsanacion-view', [AyudasSolicitadasController::class, 'subsanacionView']);
Route::get('/ayudas-solicitadas/{id}/documentos-view', [AyudasSolicitadasController::class, 'documentosView']);
Route::get('/ayudas-solicitadas/{id}/documentos-estadisticas-view', [AyudasSolicitadasController::class, 'documentosEstadisticasView']);

// Ruta para el editor visual de lógica de cuestionarios (solo admin)
Route::get('/dashboard/cuestionario-logic/{id}', function ($id) {
    $user = Auth::user();
    if (! $user->is_admin) {
        return redirect()->route('user.home');
    }
    $questionnaire = \App\Models\Questionnaire::with('questions')->findOrFail($id);
    $questions = $questionnaire->questions->map(function ($q) {
        return [
            'id' => $q->id,
            'text' => $q->text,
            'type' => $q->type,
            'options' => $q->options ?? [],
        ];
    });
    $conditions = \App\Models\QuestionCondition::where('questionnaire_id', $questionnaire->id)
        ->get(['id', 'question_id', 'operator', 'value', 'next_question_id', 'order'])
        ->map(function ($c) {
            return [
                'id' => $c->id,
                'question_id' => $c->question_id,
                'operator' => $c->operator,
                'value' => is_array($c->value) ? json_encode($c->value) : $c->value,
                'next_question_id' => $c->next_question_id,
                'order' => $c->order,
            ];
        });

    return view('admin.questionnaire-logic', [
        'questionnaire' => $questionnaire,
        'questions' => $questions,
        'conditions' => $conditions,
    ]);
})->middleware('auth')->name('admin.questionnaire.logic');

Route::get('/ayudas/slug-exists/{slug}', [AyudasController::class, 'slugExists'])->middleware('auth')->name('ayudas.slug.exists');
Route::post('/api/ayudas/{ayuda}/verify-prerequisites', [AyudasController::class, 'verifyPrerequisites'])->middleware('auth')->name('ayudas.verify-prerequisites');
Route::get('/api/ayudas/{ayuda}/has-prerequisites', [AyudasController::class, 'hasPrerequisites'])->middleware('auth')->name('ayudas.has-prerequisites');
Route::get('/api/ayudas/{ayuda}/missing-answer/{questionId}', [AyudasController::class, 'getMissingAnswer'])->middleware('auth')->name('ayudas.missing-answer');
Route::post('/api/ayudas/{ayuda}/save-answer', [AyudasController::class, 'saveAnswer'])->middleware('auth')->name('ayudas.save-answer');

// routes/web.php
use App\Http\Controllers\LiquidacionesController;

Route::prefix('operativa/liquidaciones')
    ->middleware(['auth'])
    ->name('operativa.liquidaciones.')
    ->group(function () {
        Route::get('/', [LiquidacionesController::class, 'index'])->name('index'); // tab=concesiones|pagos
        Route::put('/{contratacion}/concedida', [LiquidacionesController::class, 'updateConcedida'])->name('updateConcedida');
        Route::put('/{contratacion}/montos', [LiquidacionesController::class, 'updateMontos'])->name('updateMontos');

        // NUEVO: registrar pago de administración
        Route::post('/{contratacion}/pagos-admin', [LiquidacionesController::class, 'storePagoAdmin'])
            ->name('pagosAdmin.store');

        // (Opcional) generar cobro directo desde un pago ya guardado
        Route::post('/pagos-admin/{pago}/generar-cobro', [LiquidacionesController::class, 'generarCobroDesdePago'])
            ->name('pagosAdmin.generarCobro');
    });

Route::get(
    '/operativa/liquidaciones/{contratacion}/pagos-admin/list',
    [LiquidacionesController::class, 'listPagosAdmin']
)->middleware(['auth'])
    ->name('operativa.liquidaciones.pagosAdmin.list');

Route::post(
    '/operativa/liquidaciones/pagos-admin/{pago}/marcar-cobrada',
    [LiquidacionesController::class, 'marcarPagoComisionCobrada']
)->middleware(['auth'])->name('operativa.liquidaciones.pagosAdmin.marcarCobrada');

// Generar factura de la comisión de un pago concreto
// Route::post('/operativa/liquidaciones/pagos-admin/{pago}/facturar',
//   [LiquidacionesController::class, 'generarFacturaDesdePago']
// )->name('operativa.liquidaciones.pagosAdmin.facturar');
Route::post('/operativa/liquidaciones/pagos-admin/{pago}/facturar', function ($pago) {
    Log::debug('HIT /facturar route', ['pago' => $pago, 'ts' => now()->toDateTimeString()]);

    return app(\App\Http\Controllers\LiquidacionesController::class)->generarFacturaDesdePago($pago);
})->name('operativa.liquidaciones.pagosAdmin.facturar');

// routes/web.php
// routes/web.php
Route::get('/facturas/{pago}/ver', [App\Http\Controllers\FacturasController::class, 'ver'])
    ->name('facturas.ver');

Route::get('/contrataciones/{contratacion}/json', [\App\Http\Controllers\ContratacionController::class, 'showJson'])
    ->middleware('auth')
    ->name('contrataciones.show.json');

Route::post('/contrataciones/{contratacion}/motivos-subsanacion', [\App\Http\Controllers\ContratacionController::class, 'guardarMotivosSubsanacion'])
    ->middleware('auth')
    ->name('contrataciones.motivos-subsanacion');

Route::post('/contrataciones/{contratacion}/crear-motivo-subsanacion', [\App\Http\Controllers\ContratacionController::class, 'crearMotivoSubsanacion'])
    ->middleware('auth')
    ->name('contrataciones.crear-motivo-subsanacion');

Route::delete('/contrataciones/{contratacion}/motivos-subsanacion/{motivo}', [\App\Http\Controllers\ContratacionController::class, 'eliminarMotivoSubsanacion'])
    ->middleware('auth')
    ->name('contrataciones.eliminar-motivo-subsanacion');

Route::get('/api/documentos-disponibles', [\App\Http\Controllers\ContratacionController::class, 'getDocumentosDisponibles'])
    ->middleware('auth')
    ->name('api.documentos-disponibles');

Route::get('/onboarder', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return view('onboarder');
})->name('onboarder');

Route::get('/user-answers', [OnboarderController::class, 'getUserAnswers']);

Route::post('/onboarder/finish', [OnboarderController::class, 'finishOnboarder']);
Route::post('/onboarders/wizards/{wizardId}/onboarder-config', [WizardController::class, 'saveOnboarderConfig']);

// Limita abusos: 20 solicitudes por minuto por IP
Route::middleware('throttle:20,1')->group(function () {
    Route::get('/leadmagnet/descargar', [LeadMagnetController::class, 'download'])
        ->name('leadmagnet.download');
});

Route::get('/admin/ayudas/{ayuda}/pre-requisitos', function ($ayuda) {
    if (! Auth::check() || ! Auth::user()->is_admin) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $preRequisitos = AyudaPreRequisito::where('ayuda_id', $ayuda)
        ->with('rules')
        ->get()
        ->map(function ($pre) {
            return [
                'id' => $pre->id,
                'name' => $pre->name,
                'description' => $pre->description,
                'type' => $pre->type,
                'target_type' => $pre->target_type,
                'conviviente_type' => $pre->conviviente_type,
                'question_id' => $pre->question_id,
                'operator' => $pre->operator,
                'value' => $pre->value,
                'value2' => $pre->value2,
                'value_type' => $pre->value_type,
                'age_unit' => $pre->age_unit,
                'group_logic' => $pre->group_logic,
                'active' => $pre->active,
                'groupRules' => $pre->rules->map(function ($rule) {
                    return [
                        'id' => $rule->id,
                        'question_id' => $rule->question_id,
                        'operator' => $rule->operator,
                        'value' => $rule->value,
                        'value2' => $rule->value2,
                        'value_type' => $rule->value_type,
                        'age_unit' => $rule->age_unit,
                    ];
                }),
            ];
        });

    return response()->json($preRequisitos);
})->name('admin.ayudas.pre-requisitos.index');

Route::post('/admin/ayudas/{ayuda}/pre-requisitos', function (Request $request, $ayuda) {
    if (! Auth::check() || ! Auth::user()->is_admin) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $data = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'type' => 'required|in:simple,group',
        'target_type' => 'required|string',
        'conviviente_type' => 'nullable|string',
        'question_id' => 'nullable|integer',
        'operator' => 'nullable|string',
        'value' => 'nullable',
        'value2' => 'nullable',
        'value_type' => 'nullable|string',
        'age_unit' => 'nullable|string',
        'group_logic' => 'nullable|string',
        'active' => 'boolean',
        'rules' => 'nullable|array',
    ]);

    $data['ayuda_id'] = $ayuda;

    $preRequisito = AyudaPreRequisito::create($data);

    if ($data['type'] === 'group' && ! empty($data['rules'])) {
        foreach ($data['rules'] as $ruleData) {
            $preRequisito->rules()->create($ruleData);
        }
    }

    return response()->json(['success' => true, 'pre_requisito' => $preRequisito]);
})->name('admin.ayudas.pre-requisitos.store');

Route::put('/admin/ayudas/{ayuda}/pre-requisitos/{preRequisito}', function (\Illuminate\Http\Request $request, $ayuda, $preRequisito) {
    if (! Auth::check() || ! Auth::user()->is_admin) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $pre = AyudaPreRequisito::findOrFail($preRequisito);

    $data = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'type' => 'required|in:simple,group',
        'target_type' => 'required|string',
        'conviviente_type' => 'nullable|string',
        'question_id' => 'nullable|integer',
        'operator' => 'nullable|string',
        'value' => 'nullable',
        'value2' => 'nullable',
        'value_type' => 'nullable|string',
        'age_unit' => 'nullable|string',
        'group_logic' => 'nullable|string',
        'active' => 'boolean',
        'rules' => 'nullable|array',
    ]);

    $pre->update($data);

    if ($data['type'] === 'group' && isset($data['rules'])) {
        $pre->rules()->delete();
        foreach ($data['rules'] as $ruleData) {
            $pre->rules()->create($ruleData);
        }
    }

    return response()->json(['success' => true, 'pre_requisito' => $pre]);
})->name('admin.ayudas.pre-requisitos.update');

Route::delete('/admin/ayudas/{ayuda}/pre-requisitos/{preRequisito}', function ($ayuda, $preRequisito) {
    if (! Auth::check() || ! Auth::user()->is_admin) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $pre = AyudaPreRequisito::findOrFail($preRequisito);
    $pre->delete();

    return response()->json(['success' => true]);
})->name('admin.ayudas.pre-requisitos.destroy');

Route::get('/admin/pre-requisitos/questions', function () {
    if (! Auth::check() || ! Auth::user()->is_admin) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $questions = Question::select('id', 'text', 'slug', 'type', 'options')
        ->orderBy('text')
        ->get();

    return response()->json($questions);
})->name('admin.pre-requisitos.questions');

Route::get('/admin/questions/{question}', function ($question) {
    if (! Auth::check() || ! Auth::user()->is_admin) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $question = Question::select('id', 'text', 'slug', 'type', 'options')
        ->findOrFail($question);

    return response()->json($question);
})->name('admin.questions.show');
Route::get('/contrataciones/{contratacion}/flujos-disponibles', [ContratacionController::class, 'getFlujosDisponibles'])
    ->name('contrataciones.flujos-disponibles');

Route::patch('/contrataciones/{contratacion}/estados-opx', [ContratacionController::class, 'updateEstadosOPx'])
    ->middleware('auth')
    ->name('contrataciones.estados-opx');

Route::post('/contrataciones/{contratacion}/aplicar-transicion', [ContratacionController::class, 'aplicarTransicion'])
    ->name('contrataciones.aplicar-transicion');

// Motivos de rechazo
Route::get('/motivos-rechazo', [\App\Http\Controllers\MotivoRechazoController::class, 'index'])
    ->middleware('auth')
    ->name('motivos-rechazo.index');

// Rutas para configuración de documentos
Route::middleware(['auth'])->group(function () {
    Route::get('/contrataciones/{contratacion}/documentos-configurados', [\App\Http\Controllers\ContratacionController::class, 'getDocumentosConfigurados']);
    Route::post('/contrataciones/{contratacion}/configurar-documentos', [\App\Http\Controllers\ContratacionController::class, 'configurarDocumentos']);
    Route::delete('/contrataciones/{contratacion}/restablecer-documentos', [\App\Http\Controllers\ContratacionController::class, 'restablecerConfiguracionDocumentos']);
});

// Registro newsletter desde url publica
Route::get('/newsletter', [NewsletterController::class, 'show'])->name('newsletter.show');

Route::post('/newsletter', [NewsletterController::class, 'store'])
    ->middleware('throttle:20,1') // 20 peticiones/minuto, opcional
    ->name('newsletter.store');

// Ruta para guardar preguntas pre conviviente
Route::post('/store-pre-conviviente', [AyudasSolicitadasController::class, 'storeQuestionPreConviviente'])->name('storeQuestionPreConviviente');
Route::get('/convivientes-refresh/{ayuda}', [AyudasSolicitadasController::class, 'refresh'])->name('convivientes.refresh');

Mcp::web('/mcp/n8n', N8NServer::class);
