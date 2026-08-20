<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProductApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query();

        if ($search = $request->input('search')) {
            $clean = strtolower(trim($search));
            $query->where(function ($q) use ($clean) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$clean}%"])
                  ->orWhereRaw('LOWER(slug) LIKE ?', ["%{$clean}%"]);
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('active', true);
            } elseif ($status === 'inactive') {
                $query->where('active', false);
            } elseif ($status === 'featured') {
                $query->where('featured', true);
            }
        }

        $products = $query->orderBy('created_at', 'desc')->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();

        $imagePaths = $data['image_paths'] ?? [];
        if (!empty($data['image_url'])) {
            $imagePaths = [trim($data['image_url'])];
        }

        $product = Product::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? '',
            'price' => (int) $data['price'],
            'image_paths' => $imagePaths,
            'featured' => $request->boolean('featured'),
            'active' => $request->boolean('active', true),
            'is_digital' => $request->boolean('is_digital'),
            'digital_file_path' => $data['digital_file_path'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();

        $imagePaths = $product->image_paths ?? [];
        if (array_key_exists('image_url', $data)) {
            $imagePaths = !empty($data['image_url']) ? [trim($data['image_url'])] : [];
        } elseif (!empty($data['image_paths'])) {
            $imagePaths = $data['image_paths'];
        }

        $product->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? '',
            'price' => (int) $data['price'],
            'image_paths' => $imagePaths,
            'featured' => $request->boolean('featured'),
            'active' => $request->boolean('active'),
            'is_digital' => $request->boolean('is_digital'),
            'digital_file_path' => $data['digital_file_path'] ?? null,
        ]);

        $changes = \Illuminate\Support\Arr::except($product->getChanges(), ['updated_at']);
        if (!empty($changes)) {
            \App\Events\ProductUpdated::dispatch($product, $changes);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product,
        ]);
    }

    public function toggleStatus(Product $product): JsonResponse
    {
        $product->update(['active' => !$product->active]);

        $changes = \Illuminate\Support\Arr::except($product->getChanges(), ['updated_at']);
        if (!empty($changes)) {
            \App\Events\ProductUpdated::dispatch($product, $changes);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product status updated',
            'data' => [
                'id' => $product->id,
                'active' => $product->active,
            ],
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}
