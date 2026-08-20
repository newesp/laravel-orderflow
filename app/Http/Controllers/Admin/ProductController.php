<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
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
                } elseif ($status === 'digital') {
                    $query->where('is_digital', true);
                }
            }

            $products = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
            
            // Force view rendering to catch any blade/model exceptions
            $html = view('admin.products.index', compact('products'))->render();
            return response($html);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error($e);
            return response('Internal Server Error', 500);
        }
    }

    public function create(): View
    {
        return view('admin.products.create');
    }

    public function store(StoreProductRequest $request)
    {
        try {
            $data = $request->validated();

            $imagePaths = [];
            if (!empty($data['image_url'])) {
                $imagePaths[] = trim($data['image_url']);
            } elseif (!empty($data['image_paths'])) {
                $imagePaths = $data['image_paths'];
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

            return redirect()->route('admin.products.index')
                ->with('success', "Product '{$product->name}' created successfully.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error($e);
            return response('Internal Server Error', 500);
        }
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
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

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$product->name}' updated successfully.");
    }

    public function toggleActive(Product $product): RedirectResponse
    {
        $product->update([
            'active' => !$product->active,
        ]);

        $statusStr = $product->active ? 'active' : 'inactive';

        return back()->with('success', "Product '{$product->name}' is now {$statusStr}.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('info', "Product '{$name}' has been deleted.");
    }

    public function uploadImage(Request $request, \App\Services\SupabaseStorageService $storageService): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:10240'],
        ]);

        try {
            $result = $storageService->uploadProductImage($request->file('image'));
            return response()->json($result);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    public function uploadFile(Request $request, \App\Services\SupabaseStorageService $storageService): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,zip,rar,gz,tar', 'max:51200'],
        ]);

        try {
            $result = $storageService->uploadProductFile($request->file('file'));
            return response()->json($result);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
            ], 500);
        }
    }
}
