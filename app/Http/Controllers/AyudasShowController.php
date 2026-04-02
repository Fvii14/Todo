<?php

namespace App\Http\Controllers;

use App\Models\Ayuda;

class AyudasShowController extends Controller
{
    public function showProducts($id)
    {
        // Almacenar el id de la ayuda en la sesión
        session(['ayuda_id' => $id]);
        
        // Obtener la ayuda y sus productos relacionados a través de la tabla pivote
        $ayuda = Ayuda::findOrFail($id);
        $productos = $ayuda->productos()->with('servicios')->withPivot('recomendado')->get();
        
        // Separar productos por tipo de pago
        $productosOneTime = $productos->filter(function($producto) {
            return in_array($producto->payment_type, ['one_time', 'one-time', 'annual']);
        });
        
        $productosMonthly = $productos->filter(function($producto) {
            return $producto->payment_type === 'monthly';
        });

        // Devolver la vista 'planes-productos.blade.php' con los productos separados
        return view('user.planes-productos', compact('productosOneTime', 'productosMonthly', 'ayuda'));
    }
}
