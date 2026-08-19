<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TestDatabaseBootstrapper
{
    /**
     * Bootstrap the shared Storefront schema for test environments (SQLite or test PostgreSQL).
     */
    public static function bootstrap(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // Setup auth schema if not exists
            DB::statement('CREATE SCHEMA IF NOT EXISTS auth;');

            // auth.users
            DB::statement('
                CREATE TABLE IF NOT EXISTS auth.users (
                    id UUID PRIMARY KEY,
                    email VARCHAR(255) UNIQUE,
                    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
                );
            ');

            // public.profiles
            DB::statement("
                CREATE TABLE IF NOT EXISTS public.profiles (
                    id UUID PRIMARY KEY ,
                    display_name VARCHAR(255),
                    role VARCHAR(50) DEFAULT 'customer',
                    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
                );
            ");

            // public.products
            DB::statement('
                CREATE TABLE IF NOT EXISTS public.products (
                    id UUID PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    slug VARCHAR(255) UNIQUE NOT NULL,
                    description TEXT DEFAULT \'\',
                    price INTEGER NOT NULL DEFAULT 0,
                    image_paths TEXT[] DEFAULT ARRAY[]::TEXT[],
                    featured BOOLEAN NOT NULL DEFAULT FALSE,
                    active BOOLEAN NOT NULL DEFAULT TRUE,
                    is_digital BOOLEAN NOT NULL DEFAULT FALSE,
                    digital_file_path VARCHAR(500),
                    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
                );
            ');

            // public.orders
            DB::statement("
                CREATE TABLE IF NOT EXISTS public.orders (
                    id UUID PRIMARY KEY,
                    user_id UUID NOT NULL ,
                    status VARCHAR(50) NOT NULL DEFAULT 'pending',
                    total INTEGER NOT NULL DEFAULT 0,
                    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
                );
            ");

            // public.order_items
            DB::statement('
                CREATE TABLE IF NOT EXISTS public.order_items (
                    id BIGSERIAL PRIMARY KEY,
                    order_id UUID NOT NULL REFERENCES public.orders(id) ON DELETE CASCADE,
                    product_id UUID REFERENCES public.products(id) ON DELETE SET NULL,
                    product_name VARCHAR(255) NOT NULL,
                    unit_price INTEGER NOT NULL DEFAULT 0,
                    quantity INTEGER NOT NULL DEFAULT 1,
                    line_total INTEGER NOT NULL DEFAULT 0,
                    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
                );
            ');
        } else {
            // SQLite in-memory schema
            if (!Schema::hasTable('profiles')) {
                Schema::create('profiles', function (Blueprint $table) {
                    $table->uuid('id')->primary();
                    $table->string('display_name')->nullable();
                    $table->string('role')->default('customer');
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('products')) {
                Schema::create('products', function (Blueprint $table) {
                    $table->uuid('id')->primary();
                    $table->string('name');
                    $table->string('slug')->unique();
                    $table->text('description')->default('');
                    $table->integer('price')->default(0);
                    $table->json('image_paths')->nullable();
                    $table->boolean('featured')->default(false);
                    $table->boolean('active')->default(true);
                    $table->boolean('is_digital')->default(false);
                    $table->string('digital_file_path')->nullable();
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('orders')) {
                Schema::create('orders', function (Blueprint $table) {
                    $table->uuid('id')->primary();
                    $table->uuid('user_id');
                    $table->string('status')->default('pending');
                    $table->integer('total')->default(0);
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('order_items')) {
                Schema::create('order_items', function (Blueprint $table) {
                    $table->id();
                    $table->uuid('order_id');
                    $table->uuid('product_id')->nullable();
                    $table->string('product_name');
                    $table->integer('unit_price')->default(0);
                    $table->integer('quantity')->default(1);
                    $table->integer('line_total')->default(0);
                    $table->timestamps();
                });
            }
        }
    }
}
