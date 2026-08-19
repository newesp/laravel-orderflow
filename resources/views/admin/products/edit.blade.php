@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Product</h1>
            <p class="text-sm text-slate-500 mt-1">Updating: {{ $product->name }}</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
            &larr; Back to Products
        </a>
    </div>

    @if ($errors->any())
        <div class="rounded-xl bg-red-50 p-4 border border-red-200 text-red-800 text-sm">
            <div class="font-semibold mb-1">Please fix the following validation issues:</div>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Product Name *</label>
                <input type="text"
                       name="name"
                       id="name"
                       value="{{ old('name', $product->name) }}"
                       required
                       class="w-full px-3.5 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="slug" class="block text-sm font-medium text-slate-700 mb-1">Slug (URL Path) *</label>
                    <input type="text"
                           name="slug"
                           id="slug"
                           value="{{ old('slug', $product->slug) }}"
                           required
                           class="w-full px-3.5 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-slate-700 mb-1">Price (NT$ Integer) *</label>
                    <input type="number"
                           name="price"
                           id="price"
                           min="0"
                           value="{{ old('price', $product->price) }}"
                           required
                           class="w-full px-3.5 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <textarea name="description"
                          id="description"
                          rows="4"
                          class="w-full px-3.5 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">{{ old('description', $product->description) }}</textarea>
            </div>

            <div>
                <label for="image_url" class="block text-sm font-medium text-slate-700 mb-1">Image URL</label>
                <input type="url"
                       name="image_url"
                       id="image_url"
                       value="{{ old('image_url', $product->image_paths[0] ?? '') }}"
                       placeholder="https://..."
                       class="w-full px-3.5 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
            </div>

            <div class="pt-4 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <label class="flex items-center space-x-2.5 cursor-pointer">
                    <input type="checkbox" name="active" value="1" {{ old('active', $product->active) ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                    <span class="text-sm font-medium text-slate-700">Active (Visible)</span>
                </label>

                <label class="flex items-center space-x-2.5 cursor-pointer">
                    <input type="checkbox" name="featured" value="1" {{ old('featured', $product->featured) ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                    <span class="text-sm font-medium text-slate-700">Featured Product</span>
                </label>

                <label class="flex items-center space-x-2.5 cursor-pointer">
                    <input type="checkbox" name="is_digital" value="1" {{ old('is_digital', $product->is_digital) ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                    <span class="text-sm font-medium text-slate-700">Digital Product</span>
                </label>
            </div>

            <div class="p-4 bg-purple-50 rounded-xl border border-purple-200">
                <label for="digital_file_path" class="block text-sm font-medium text-purple-900 mb-1">Digital File Path</label>
                <input type="text"
                       name="digital_file_path"
                       id="digital_file_path"
                       value="{{ old('digital_file_path', $product->digital_file_path) }}"
                       placeholder="digital-products/file.zip"
                       class="w-full px-3.5 py-2 border border-purple-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-purple-500 focus:outline-none" />
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end space-x-3">
                <a href="{{ route('admin.products.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                    Update Product
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
