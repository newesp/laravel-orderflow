<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'products';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'description',
        'price',
        'image_paths',
        'featured',
        'active',
        'is_digital',
        'digital_file_path',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'featured' => 'boolean',
            'active' => 'boolean',
            'is_digital' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Handle PostgreSQL text[] array and JSON formats seamlessly.
     */
    protected function imagePaths(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (is_null($value)) {
                    return [];
                }
                if (is_array($value)) {
                    return $value;
                }
                // Check if PostgreSQL array string format e.g. {"path1","path2"}
                if (is_string($value) && str_starts_with($value, '{') && str_ends_with($value, '}')) {
                    $inner = trim($value, '{}');
                    if (empty($inner)) {
                        return [];
                    }
                    return str_getcsv($inner, ',', '"');
                }
                // Check if JSON encoded
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [];
            },
            set: function ($value) {
                if (is_array($value)) {
                    return json_encode(array_values($value));
                }
                return $value;
            }
        );
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'NT$ ' . number_format((float) $this->price);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }
}
