<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('integration_logs')) {
            Schema::create('integration_logs', function (Blueprint $table) {
                $table->id();
                $table->string('event_type');
                $table->string('reference_type');
                $table->string('reference_id');
                $table->string('target')->default('system');
                $table->string('status')->default('success'); // success, failed, skipped
                $table->json('payload')->nullable();
                $table->json('response')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['reference_type', 'reference_id']);
                $table->index('event_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
    }
};
