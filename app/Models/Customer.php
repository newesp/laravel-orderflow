<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'admin_customer_view';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'email',
        'display_name',
        'role',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'id', 'id');
    }

    public function getTotalSpentAttribute(): int
    {
        if (array_key_exists('total_spent', $this->attributes)) {
            return (int) $this->attributes['total_spent'];
        }

        if ($this->relationLoaded('orders')) {
            return (int) $this->orders
                ->whereIn('status', ['processing', 'completed'])
                ->sum('total');
        }

        return (int) $this->orders()
            ->whereIn('status', ['processing', 'completed'])
            ->sum('total');
    }

    public function getFormattedTotalSpentAttribute(): string
    {
        return 'NT$ ' . number_format($this->total_spent);
    }
}
