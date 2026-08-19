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
        <form id="product_form" method="POST" action="{{ route('admin.products.update', $product) }}" class="space-y-6">
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

            <!-- Product Image Upload Section -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Product Image</label>
                
                @php
                    $currentImageUrl = old('image_url', $product->image_paths[0] ?? '');
                @endphp

                <!-- Hidden input storing URL -->
                <input type="hidden" name="image_url" id="image_url" value="{{ $currentImageUrl }}">

                <!-- Hidden native file input -->
                <input type="file" id="product_image_file_input" accept="image/*" class="hidden">

                <!-- Upload trigger box (styled like modern-storefront FileInput) -->
                <div id="product_image_dropzone"
                     onclick="document.getElementById('product_image_file_input').click()"
                     class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg hover:border-slate-400 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 cursor-pointer transition flex items-center justify-between group">
                    <div class="flex items-center space-x-2.5 overflow-hidden">
                        <!-- Icon -->
                        <div id="product_image_icon_wrapper" class="text-slate-400 group-hover:text-slate-600 transition flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <!-- Placeholder / Filename text -->
                        <span id="product_image_placeholder" class="text-sm {{ $currentImageUrl ? 'text-slate-700 font-medium' : 'text-slate-400 group-hover:text-slate-500' }} transition select-none truncate">
                            {{ $currentImageUrl ? 'Current image: ' . basename($currentImageUrl) : 'Click to upload image to Supabase Storage' }}
                        </span>
                    </div>
                    <div id="product_image_status_badge" class="hidden text-xs font-medium text-indigo-600 flex-shrink-0 ml-2">
                    </div>
                </div>

                <!-- Error message for image upload -->
                <p id="product_image_error" class="text-xs text-red-600 mt-1.5 hidden"></p>

                <!-- Image Preview -->
                <div id="product_image_preview_box" class="mt-3 {{ $currentImageUrl ? '' : 'hidden' }}">
                    <p class="text-xs text-slate-500 font-medium mb-1.5">Image Preview:</p>
                    <div class="relative inline-block border border-slate-200 rounded-lg p-1.5 bg-slate-50 shadow-xs">
                        <img id="product_image_preview_img"
                             src="{{ $currentImageUrl }}"
                             alt="Product preview"
                             class="h-28 w-auto max-w-full rounded-md object-contain" />
                        <button type="button"
                                onclick="removeProductImage(event)"
                                class="absolute -top-2 -right-2 bg-slate-700 hover:bg-red-600 text-white rounded-full p-1 shadow-md transition"
                                title="Remove Image">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Status Checkboxes -->
            <div class="pt-4 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <label class="flex items-center space-x-2.5 cursor-pointer">
                    <input type="checkbox" name="is_digital" id="is_digital" value="1" {{ old('is_digital', $product->is_digital) ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300" onchange="toggleDigitalSection()">
                    <span class="text-sm font-medium text-slate-700">Is Digital Product?</span>
                </label>

                <label class="flex items-center space-x-2.5 cursor-pointer">
                    <input type="checkbox" name="featured" value="1" {{ old('featured', $product->featured) ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                    <span class="text-sm font-medium text-slate-700">Featured Product (Show on homepage)</span>
                </label>

                <label class="flex items-center space-x-2.5 cursor-pointer">
                    <input type="checkbox" name="active" value="1" {{ old('active', $product->active) ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                    <span class="text-sm font-medium text-slate-700">Active (Visible to customers)</span>
                </label>
            </div>

            @php
                $currentDigitalPath = old('digital_file_path', $product->digital_file_path);
                $isDigitalChecked = old('is_digital', $product->is_digital);
            @endphp

            <!-- Digital Product File Section -->
            <div id="digital_fields" class="p-4 bg-purple-50/70 rounded-xl border border-purple-200 space-y-3 {{ $isDigitalChecked ? '' : 'hidden' }}">
                <div>
                    <label class="block text-sm font-medium text-purple-900 mb-1.5">Digital Product File (.pdf, .zip, .rar)</label>
                    
                    <!-- Hidden input storing digital file path -->
                    <input type="hidden" name="digital_file_path" id="digital_file_path" value="{{ $currentDigitalPath }}">

                    <!-- Hidden native file input -->
                    <input type="file" id="digital_file_input" accept=".pdf,.zip,.rar,application/pdf,application/zip,application/vnd.rar,application/x-rar-compressed,application/octet-stream" class="hidden">

                    <!-- Upload trigger box (styled like modern-storefront FileInput) -->
                    <div id="digital_file_dropzone"
                         onclick="document.getElementById('digital_file_input').click()"
                         class="w-full px-3.5 py-2.5 bg-white border border-purple-300 rounded-lg hover:border-purple-400 focus-within:border-purple-500 focus-within:ring-1 focus-within:ring-purple-500 cursor-pointer transition flex items-center justify-between group">
                        <div class="flex items-center space-x-2.5 overflow-hidden">
                            <!-- Icon -->
                            <div id="digital_file_icon_wrapper" class="text-purple-400 group-hover:text-purple-600 transition flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                            </div>
                            <!-- Placeholder / Filename text -->
                            <span id="digital_file_placeholder" class="text-sm {{ $currentDigitalPath ? 'text-slate-700 font-medium' : 'text-slate-400 group-hover:text-slate-500' }} transition select-none truncate">
                                {{ $currentDigitalPath ? 'Uploaded: ' . basename($currentDigitalPath) : 'Click to upload digital file' }}
                            </span>
                        </div>
                        <div id="digital_file_status_badge" class="hidden text-xs font-medium text-purple-700 flex-shrink-0 ml-2">
                        </div>
                    </div>

                    <!-- Error message for digital file upload -->
                    <p id="digital_file_error" class="text-xs text-red-600 mt-1.5 hidden"></p>

                    <!-- Current file description badge -->
                    <div id="digital_file_description" class="mt-2 {{ $currentDigitalPath ? 'flex' : 'hidden' }} items-center justify-between text-xs text-purple-700 bg-purple-100/70 px-3 py-1.5 rounded-md border border-purple-200">
                        <div class="flex items-center space-x-1.5 overflow-hidden">
                            <svg class="w-4 h-4 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span id="digital_file_display_name" class="font-mono truncate">
                                Current file: {{ $currentDigitalPath ? basename($currentDigitalPath) : '' }}
                            </span>
                        </div>
                        <button type="button"
                                onclick="removeDigitalFile(event)"
                                class="text-purple-600 hover:text-red-600 font-semibold text-xs ml-2 hover:underline flex-shrink-0">
                            Remove
                        </button>
                    </div>

                    <p class="text-xs text-purple-600/90 mt-1.5">Relative path in Supabase private bucket. Customers with completed orders can securely download this file.</p>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end space-x-3">
                <a href="{{ route('admin.products.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" id="submit_btn" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Update Product
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    let activeUploads = 0;
    const submitBtn = document.getElementById('submit_btn');

    function updateSubmitButtonState() {
        if (activeUploads > 0) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    function toggleDigitalSection() {
        const isDigital = document.getElementById('is_digital').checked;
        const digitalFields = document.getElementById('digital_fields');
        if (isDigital) {
            digitalFields.classList.remove('hidden');
        } else {
            digitalFields.classList.add('hidden');
        }
    }

    // --- Form Submission Logic ---
    const productForm = document.getElementById('product_form');
    productForm.addEventListener('submit', function(e) {
        if (activeUploads > 0) {
            e.preventDefault();
            alert('Please wait for file uploads to complete before submitting.');
            return false;
        }

        const priceInput = document.getElementById('price');
        if (parseInt(priceInput.value) === 0) {
            if (!confirm('Price is set to 0. Are you sure you want to update this to a free product?')) {
                e.preventDefault();
                return false;
            }
        }
    });

    // --- Product Image Upload Logic ---
    const imageInput = document.getElementById('product_image_file_input');
    const imageDropzone = document.getElementById('product_image_dropzone');
    const imageUrlInput = document.getElementById('image_url');
    const imagePreviewBox = document.getElementById('product_image_preview_box');
    const imagePreviewImg = document.getElementById('product_image_preview_img');
    const imageError = document.getElementById('product_image_error');
    const imageIconWrapper = document.getElementById('product_image_icon_wrapper');
    const imagePlaceholder = document.getElementById('product_image_placeholder');

    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            uploadImageFile(this.files[0]);
        }
    });

    imageDropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        imageDropzone.classList.add('border-indigo-500', 'bg-indigo-50/40');
    });
    imageDropzone.addEventListener('dragleave', () => {
        imageDropzone.classList.remove('border-indigo-500', 'bg-indigo-50/40');
    });
    imageDropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        imageDropzone.classList.remove('border-indigo-500', 'bg-indigo-50/40');
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            uploadImageFile(e.dataTransfer.files[0]);
        }
    });

    function uploadImageFile(file) {
        if (!file.type.startsWith('image/')) {
            showImageError('Please select a valid image file (JPG, PNG, WebP, etc.)');
            return;
        }
        imageError.classList.add('hidden');
        activeUploads++;
        updateSubmitButtonState();

        // Loading state
        imageIconWrapper.innerHTML = `
            <svg class="animate-spin w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        `;
        imagePlaceholder.textContent = 'Uploading image to Supabase Storage...';
        imagePlaceholder.classList.add('text-indigo-600');
        imageDropzone.classList.add('opacity-75', 'cursor-wait');

        const formData = new FormData();
        formData.append('image', file);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('input[name="_token"]')?.value;

        fetch("{{ route('admin.products.upload-image') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Upload failed');
            }
            return data;
        })
        .then((data) => {
            imageUrlInput.value = data.url;
            imagePreviewImg.src = data.url;
            imagePreviewBox.classList.remove('hidden');
            imagePlaceholder.textContent = `Uploaded: ${data.file_name || file.name}`;
            imagePlaceholder.classList.remove('text-indigo-600', 'text-slate-400');
            imagePlaceholder.classList.add('text-slate-700', 'font-medium');
        })
        .catch((err) => {
            showImageError(err.message || 'Failed to upload image.');
            imagePlaceholder.textContent = 'Click to upload image to Supabase Storage';
            imagePlaceholder.classList.remove('text-indigo-600');
            imagePlaceholder.classList.add('text-slate-400');
        })
        .finally(() => {
            imageIconWrapper.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
            `;
            imageDropzone.classList.remove('opacity-75', 'cursor-wait');
            imageInput.value = '';
            activeUploads--;
            updateSubmitButtonState();
        });
    }

    function showImageError(msg) {
        imageError.textContent = msg;
        imageError.classList.remove('hidden');
    }

    function removeProductImage(event) {
        if (event) event.stopPropagation();
        imageUrlInput.value = '';
        imagePreviewImg.src = '';
        imagePreviewBox.classList.add('hidden');
        imagePlaceholder.textContent = 'Click to upload image to Supabase Storage';
        imagePlaceholder.classList.remove('text-slate-700', 'font-medium');
        imagePlaceholder.classList.add('text-slate-400');
        imageError.classList.add('hidden');
    }

    // --- Digital Product File Upload Logic ---
    const digitalFileInput = document.getElementById('digital_file_input');
    const digitalFileDropzone = document.getElementById('digital_file_dropzone');
    const digitalFilePathInput = document.getElementById('digital_file_path');
    const digitalFileDescription = document.getElementById('digital_file_description');
    const digitalFileDisplayName = document.getElementById('digital_file_display_name');
    const digitalFileError = document.getElementById('digital_file_error');
    const digitalFileIconWrapper = document.getElementById('digital_file_icon_wrapper');
    const digitalFilePlaceholder = document.getElementById('digital_file_placeholder');

    digitalFileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            uploadDigitalProductFile(this.files[0]);
        }
    });

    digitalFileDropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        digitalFileDropzone.classList.add('border-purple-500', 'bg-purple-50/40');
    });
    digitalFileDropzone.addEventListener('dragleave', () => {
        digitalFileDropzone.classList.remove('border-purple-500', 'bg-purple-50/40');
    });
    digitalFileDropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        digitalFileDropzone.classList.remove('border-purple-500', 'bg-purple-50/40');
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            uploadDigitalProductFile(e.dataTransfer.files[0]);
        }
    });

    function uploadDigitalProductFile(file) {
        digitalFileError.classList.add('hidden');
        activeUploads++;
        updateSubmitButtonState();

        digitalFileIconWrapper.innerHTML = `
            <svg class="animate-spin w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        `;
        digitalFilePlaceholder.textContent = 'Uploading digital file to Supabase Storage...';
        digitalFilePlaceholder.classList.add('text-purple-600');
        digitalFileDropzone.classList.add('opacity-75', 'cursor-wait');

        const formData = new FormData();
        formData.append('file', file);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('input[name="_token"]')?.value;

        fetch("{{ route('admin.products.upload-file') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Upload failed');
            }
            return data;
        })
        .then((data) => {
            digitalFilePathInput.value = data.path;
            const displayFilename = data.file_name || data.path.split('/').pop();
            digitalFileDisplayName.textContent = `Current file: ${displayFilename}`;
            digitalFileDescription.classList.remove('hidden');
            digitalFileDescription.classList.add('flex');
            digitalFilePlaceholder.textContent = `Uploaded: ${displayFilename}`;
            digitalFilePlaceholder.classList.remove('text-purple-600', 'text-slate-400');
            digitalFilePlaceholder.classList.add('text-slate-700', 'font-medium');
        })
        .catch((err) => {
            showDigitalFileError(err.message || 'Failed to upload digital file.');
            digitalFilePlaceholder.textContent = 'Click to upload digital file';
            digitalFilePlaceholder.classList.remove('text-purple-600');
            digitalFilePlaceholder.classList.add('text-slate-400');
        })
        .finally(() => {
            digitalFileIconWrapper.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
            `;
            digitalFileDropzone.classList.remove('opacity-75', 'cursor-wait');
            digitalFileInput.value = '';
            activeUploads--;
            updateSubmitButtonState();
        });
    }

    function showDigitalFileError(msg) {
        digitalFileError.textContent = msg;
        digitalFileError.classList.remove('hidden');
    }

    function removeDigitalFile(event) {
        if (event) event.stopPropagation();
        digitalFilePathInput.value = '';
        digitalFileDescription.classList.add('hidden');
        digitalFileDescription.classList.remove('flex');
        digitalFilePlaceholder.textContent = 'Click to upload digital file';
        digitalFilePlaceholder.classList.remove('text-slate-700', 'font-medium');
        digitalFilePlaceholder.classList.add('text-slate-400');
        digitalFileError.classList.add('hidden');
    }
</script>
@endsection
