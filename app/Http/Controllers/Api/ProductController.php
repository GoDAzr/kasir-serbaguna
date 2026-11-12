<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Daftar produk. Query: search, category_id, low_stock=1, active=1, per_page
     */
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->search($request->query('search'))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->query('category_id')))
            ->when($request->boolean('low_stock'), fn ($q) => $q->lowStock())
            ->when($request->boolean('active'), fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->paginate(min((int) $request->query('per_page', 20), 100));

        return response()->json($products);
    }

    public function show(Product $product)
    {
        return response()->json($product->load('category'));
    }

    /**
     * Cari produk dari hasil scan barcode / SKU (dipakai kamera Android).
     */
    public function byBarcode(string $code)
    {
        $product = Product::with('category')
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('barcode', $code)->orWhere('sku', $code))
            ->first();

        if (! $product) {
            return response()->json(['message' => 'Produk tidak ditemukan.'], 404);
        }

        return response()->json($product);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'sku' => 'required|string|max:50|unique:products,sku,NULL,id,business_id,' . $request->user()->business_id,
            'barcode' => 'nullable|string|max:50|unique:products,barcode,NULL,id,business_id,' . $request->user()->business_id,
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $product = DB::transaction(function () use ($data, $request) {
            $initial = (int) ($data['stock'] ?? 0);
            $product = Product::create(array_merge($data, ['stock' => 0]));

            // Stok awal dicatat di riwayat stok & gudang utama
            if ($initial > 0) {
                StockService::move($product->id, StockService::defaultWarehouse(), $initial, 'in', ['note' => 'Stok awal']);
                $product->refresh();
            }

            return $product;
        });

        return response()->json($product->load('category'), 201);
    }

    public function update(Request $request, Product $product)
    {
        // Stok tidak diubah lewat sini, gunakan endpoint /products/{id}/stock
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'sku' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product->id)],
            'barcode' => ['nullable', 'string', 'max:50', Rule::unique('products', 'barcode')->ignore($product->id)],
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $product->update($data);

        return response()->json($product->load('category'));
    }

    public function destroy(Product $product)
    {
        // Soft delete: riwayat transaksi lama tetap aman
        $product->delete();

        return response()->json(['message' => 'Produk dihapus.']);
    }
}
