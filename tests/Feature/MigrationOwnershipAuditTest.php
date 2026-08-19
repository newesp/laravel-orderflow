<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\Support\TestDatabaseBootstrapper;
use Tests\TestCase;

class MigrationOwnershipAuditTest extends TestCase
{
    public function test_laravel_migrations_do_not_drop_shared_storefront_tables_on_rollback(): void
    {
        // 1. Ensure shared storefront tables exist
        TestDatabaseBootstrapper::bootstrap();
        $this->assertTrue(Schema::hasTable('profiles'));
        $this->assertTrue(Schema::hasTable('products'));
        $this->assertTrue(Schema::hasTable('orders'));
        $this->assertTrue(Schema::hasTable('order_items'));

        // 2. Run Laravel production migrations
        Artisan::call('migrate');
        $this->assertTrue(Schema::hasTable('integration_logs'));

        // 3. Rollback Laravel migrations
        Artisan::call('migrate:rollback');

        // 4. Verify shared Storefront tables are STILL intact
        $this->assertTrue(Schema::hasTable('profiles'), 'profiles table must not be dropped by Laravel rollback');
        $this->assertTrue(Schema::hasTable('products'), 'products table must not be dropped by Laravel rollback');
        $this->assertTrue(Schema::hasTable('orders'), 'orders table must not be dropped by Laravel rollback');
        $this->assertTrue(Schema::hasTable('order_items'), 'order_items table must not be dropped by Laravel rollback');

        // Laravel-owned tables are rolled back
        $this->assertFalse(Schema::hasTable('integration_logs'), 'integration_logs should be dropped on rollback');
    }
}
