<?php

namespace App\Services;

use App\Models\AyudaProducto;
use App\Models\Product;
use App\Models\Servicio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService
{
    /**
     * Convierte el precio de euros a centavos.
     * Asume que todos los precios recibidos están en euros y los convierte a centavos.
     * Los precios en la BD siempre se almacenan en centavos.
     * 
     * @param float|null $price Precio en euros
     * @return float|null Precio en centavos
     */
    private function convertEurosToCents($price)
    {
        if ($price === null || $price == 0) {
            return null;
        }

        // Siempre convertir de euros a centavos multiplicando por 100
        return round($price * 100, 2);
    }

    /**
     * Obtener todos los productos
     */
    public function getAllProducts(): array
    {
        try {
            $products = Product::with('servicios')->orderBy('product_name', 'asc')->get();
            return $products->toArray();
        } catch (\Exception $e) {
            Log::error('Error obteniendo productos: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Crear un nuevo producto
     */
    public function createProduct(array $data): Products
    {
        try {
            DB::beginTransaction();

            // Convertir precio de euros a centavos antes de guardar
            // Los precios siempre se reciben en euros y se almacenan en centavos en la BD
            $normalizedPrice = isset($data['price']) ? $this->convertEurosToCents($data['price']) : null;

            $product = Product::create([
                'product_name' => $data['product_name'],
                'stripe_product_id' => $data['stripe_product_id'] ?? null,
                'price_id' => $data['price_id'] ?? null,
                'price' => $normalizedPrice,
                'commission_pct' => $data['commission_pct'] ?? null,
                'currency' => $data['currency'] ?? 'EUR',
                'payment_type' => $data['payment_type'] ?? null,
            ]);

            // Asociar servicios si se proporcionan
            if (isset($data['servicios']) && is_array($data['servicios'])) {
                $serviciosData = [];
                foreach ($data['servicios'] as $index => $servicioId) {
                    $serviciosData[$servicioId] = ['orden' => $index];
                }
                $product->servicios()->sync($serviciosData);
            }

            DB::commit();

            return $product->load('servicios');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creando producto: '.$e->getMessage(), ['data' => $data]);
            throw $e;
        }
    }

    /**
     * Actualizar un producto existente
     */
    public function updateProduct(int $productId, array $data): Products
    {
        try {
            DB::beginTransaction();

            $product = Product::findOrFail($productId);
            
            // Convertir precio de euros a centavos si se está actualizando
            // Los precios siempre se reciben en euros y se almacenan en centavos en la BD
            $normalizedPrice = isset($data['price']) 
                ? $this->convertEurosToCents($data['price']) 
                : $product->price;

            $product->update([
                'product_name' => $data['product_name'] ?? $product->product_name,
                'stripe_product_id' => $data['stripe_product_id'] ?? $product->stripe_product_id,
                'price_id' => $data['price_id'] ?? $product->price_id,
                'price' => $normalizedPrice,
                'commission_pct' => $data['commission_pct'] ?? $product->commission_pct,
                'currency' => $data['currency'] ?? $product->currency,
                'payment_type' => $data['payment_type'] ?? $product->payment_type,
            ]);

            // Actualizar servicios si se proporcionan
            if (isset($data['servicios']) && is_array($data['servicios'])) {
                $serviciosData = [];
                foreach ($data['servicios'] as $index => $servicioId) {
                    $serviciosData[$servicioId] = ['orden' => $index];
                }
                $product->servicios()->sync($serviciosData);
            }

            DB::commit();

            return $product->fresh()->load('servicios');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando producto: '.$e->getMessage(), [
                'product_id' => $productId,
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Obtener un producto por ID
     */
    public function getProductById(int $productId): Products
    {
        return Product::with('servicios')->findOrFail($productId);
    }

    /**
     * Asociar productos a una ayuda
     */
    public function associateProductsToAyuda(int $ayudaId, array $productsData): void
    {
        try {
            DB::beginTransaction();

            // Eliminar asociaciones existentes
            AyudaProducto::where('ayuda_id', $ayudaId)->delete();

            // Crear nuevas asociaciones
            foreach ($productsData as $productData) {
                // Si es un array con product_id y recomendado
                if (is_array($productData)) {
                    AyudaProducto::create([
                        'ayuda_id' => $ayudaId,
                        'product_id' => $productData['product_id'] ?? $productData['id'] ?? $productData,
                        'recomendado' => $productData['recomendado'] ?? false,
                    ]);
                } else {
                    // Si es solo un ID (compatibilidad hacia atrás)
                    AyudaProducto::create([
                        'ayuda_id' => $ayudaId,
                        'product_id' => $productData,
                        'recomendado' => false,
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error asociando productos a ayuda: '.$e->getMessage(), [
                'ayuda_id' => $ayudaId,
                'products_data' => $productsData,
            ]);
            throw $e;
        }
    }

    /**
     * Obtener productos asociados a una ayuda
     */
    public function getProductsByAyudaId(int $ayudaId): array
    {
        try {
            $ayudaProductos = AyudaProducto::where('ayuda_id', $ayudaId)
                ->with('product')
                ->get();

            return $ayudaProductos->pluck('product')->filter()->toArray();
        } catch (\Exception $e) {
            Log::error('Error obteniendo productos de ayuda: '.$e->getMessage(), [
                'ayuda_id' => $ayudaId,
            ]);
            throw $e;
        }
    }

    /**
     * Obtener IDs de productos asociados a una ayuda
     */
    public function getProductIdsByAyudaId(int $ayudaId): array
    {
        try {
            return AyudaProducto::where('ayuda_id', $ayudaId)
                ->pluck('product_id')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error obteniendo IDs de productos de ayuda: '.$e->getMessage(), [
                'ayuda_id' => $ayudaId,
            ]);
            throw $e;
        }
    }
}
