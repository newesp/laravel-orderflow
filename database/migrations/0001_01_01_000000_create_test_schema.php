<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment('testing') || app()->runningUnitTests()) {
            \Tests\Support\TestDatabaseBootstrapper::bootstrap();
        }
    }

    public function down(): void
    {
    }
};
