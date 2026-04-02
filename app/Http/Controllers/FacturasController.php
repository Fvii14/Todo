<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class FacturasController extends Controller
{
    public function ver(int $pagoId)
    {
        $pago = DB::table('pagos_administracion')->where('id', $pagoId)->firstOrFail();
        abort_if(empty($pago->factura_pdf_gcs_path), 404);
        /** @var \App\Services\GcsUploaderService $uploader */
        $uploader = app(\App\Services\GcsUploaderService::class);
        $url = $uploader->getTemporaryUrl($pago->factura_pdf_gcs_path, 60); // 60 min

        return redirect()->away($url);
    }
}
