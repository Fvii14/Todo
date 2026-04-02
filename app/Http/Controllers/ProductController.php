<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Obtener todos los productos
     */
    public function index(): JsonResponse
    {
        try {
            $products = $this->productService->getAllProducts();

            return response()->json([
                'success' => true,
                'products' => $products,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en ProductController@index: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los productos',
            ], 500);
        }
    }

    /**
     * Crear un nuevo producto
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_name' => 'required|string|max:255',
                'stripe_product_id' => 'nullable|string|max:255',
                'price_id' => 'nullable|string|max:255',
                'price' => 'nullable|numeric|min:0', // Precio en euros (se convierte a céntimos en el servicio)
                'commission_pct' => 'nullable|numeric|min:0|max:100',
                'currency' => 'nullable|string|max:3',
                'payment_type' => 'nullable|string|max:255',
                'servicios' => 'nullable|array',
                'servicios.*' => 'integer|exists:servicios,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $product = $this->productService->createProduct($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Producto creado correctamente',
                'product' => $product,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error en ProductController@store: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el producto',
            ], 500);
        }
    }

    /**
     * Actualizar un producto existente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_name' => 'sometimes|required|string|max:255',
                'stripe_product_id' => 'nullable|string|max:255',
                'price_id' => 'nullable|string|max:255',
                'price' => 'nullable|numeric|min:0', // Precio en euros (se convierte a céntimos en el servicio)
                'commission_pct' => 'nullable|numeric|min:0|max:100',
                'currency' => 'nullable|string|max:3',
                'payment_type' => 'nullable|string|max:255',
                'servicios' => 'nullable|array',
                'servicios.*' => 'integer|exists:servicios,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $product = $this->productService->updateProduct($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado correctamente',
                'product' => $product,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en ProductController@update: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el producto',
            ], 500);
        }
    }

    /**
     * Obtener un producto por ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $product = $this->productService->getProductById($id);

            return response()->json([
                'success' => true,
                'product' => $product,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en ProductController@show: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado',
            ], 404);
        }
    }

    /**
     * Asociar productos a una ayuda
     */
    public function associateToAyuda(Request $request, int $ayudaId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'products' => 'required|array',
                'products.*.product_id' => 'required|integer|exists:products,id',
                'products.*.recomendado' => 'nullable|boolean',
            ]);

            // Compatibilidad: también aceptar product_ids como array simple
            if ($request->has('product_ids') && !$request->has('products')) {
                $products = array_map(function($id) {
                    return ['product_id' => $id, 'recomendado' => false];
                }, $request->input('product_ids'));
                $request->merge(['products' => $products]);
            }

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $this->productService->associateProductsToAyuda(
                $ayudaId,
                $request->input('products')
            );

            return response()->json([
                'success' => true,
                'message' => 'Productos asociados correctamente a la ayuda',
            ]);
        } catch (\Exception $e) {
            Log::error('Error en ProductController@associateToAyuda: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al asociar productos a la ayuda',
            ], 500);
        }
    }

    /**
     * Obtener productos asociados a una ayuda
     */
    public function getByAyuda(int $ayudaId): JsonResponse
    {
        try {
            $products = $this->productService->getProductsByAyudaId($ayudaId);

            return response()->json([
                'success' => true,
                'products' => $products,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en ProductController@getByAyuda: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos de la ayuda',
            ], 500);
        }
    }
}
