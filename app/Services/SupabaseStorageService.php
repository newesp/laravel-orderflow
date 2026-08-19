<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class SupabaseStorageService
{
    protected ?string $supabaseUrl;
    protected ?string $supabaseKey;

    public function __construct()
    {
        $this->supabaseUrl = config('services.supabase.url') ?? env('SUPABASE_URL');
        $this->supabaseKey = config('services.supabase.service_role_key')
            ?? config('services.supabase.anon_key')
            ?? env('SUPABASE_SERVICE_ROLE_KEY')
            ?? env('SUPABASE_ANON_KEY');
    }

    public function uploadProductImage(UploadedFile $file): array
    {
        $fileExt = $file->getClientOriginalExtension() ?: 'jpg';
        $fileName = time() . '-' . Str::random(10) . '.' . $fileExt;
        $filePath = 'products/' . $fileName;
        $mimeType = $file->getMimeType() ?: 'image/jpeg';

        if ($this->isSupabaseConfigured()) {
            $url = rtrim($this->supabaseUrl, '/') . '/storage/v1/object/product-images/' . $filePath;
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->supabaseKey,
                'apikey' => $this->supabaseKey,
                'Content-Type' => $mimeType,
                'cache-control' => '3600',
                'x-upsert' => 'false',
            ])->withBody($file->getContent(), $mimeType)->post($url);

            if (!$response->successful()) {
                Log::error('Supabase image upload failed: ' . $response->body());
                throw new RuntimeException('Failed to upload image to Supabase Storage: ' . $response->status() . ' ' . $response->body());
            }

            $publicUrl = rtrim($this->supabaseUrl, '/') . '/storage/v1/object/public/product-images/' . $filePath;

            return [
                'success' => true,
                'url' => $publicUrl,
                'path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
            ];
        }

        // Fallback to local public disk if Supabase credentials are not present
        $savedPath = $file->storeAs('products', $fileName, 'public');
        $publicUrl = Storage::disk('public')->url($savedPath);

        return [
            'success' => true,
            'url' => $publicUrl,
            'path' => $savedPath,
            'file_name' => $file->getClientOriginalName(),
        ];
    }

    public function uploadProductFile(UploadedFile $file): array
    {
        $fileExt = $file->getClientOriginalExtension() ?: 'zip';
        $fileName = time() . '-' . Str::random(10) . '.' . $fileExt;
        $filePath = 'digital-products/' . $fileName;
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        if ($this->isSupabaseConfigured()) {
            $url = rtrim($this->supabaseUrl, '/') . '/storage/v1/object/product-files/' . $filePath;
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->supabaseKey,
                'apikey' => $this->supabaseKey,
                'Content-Type' => $mimeType,
                'cache-control' => '3600',
                'x-upsert' => 'false',
            ])->withBody($file->getContent(), $mimeType)->post($url);

            if (!$response->successful()) {
                Log::error('Supabase digital file upload failed: ' . $response->body());
                throw new RuntimeException('Failed to upload digital file to Supabase Storage: ' . $response->status() . ' ' . $response->body());
            }

            return [
                'success' => true,
                'path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
            ];
        }

        // Fallback to local storage
        $savedPath = $file->storeAs('digital-products', $fileName, 'local');

        return [
            'success' => true,
            'path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
        ];
    }

    public function isSupabaseConfigured(): bool
    {
        return !empty($this->supabaseUrl) && !empty($this->supabaseKey);
    }
}
