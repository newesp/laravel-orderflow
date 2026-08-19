<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For testing/local SQLite vs Supabase PostgreSQL
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("
                CREATE OR REPLACE VIEW public.admin_customer_view AS
                SELECT 
                    COALESCE(u.id, p.id) AS id,
                    u.email AS email,
                    COALESCE(p.display_name, split_part(u.email, '@', 1)) AS display_name,
                    COALESCE(p.role, 'customer') AS role,
                    COALESCE(p.created_at, u.created_at, NOW()) AS created_at,
                    COALESCE(p.updated_at, NOW()) AS updated_at
                FROM public.profiles p
                FULL OUTER JOIN auth.users u ON u.id = p.id;
            ");
        } else {
            // SQLite / MySQL view representation
            DB::statement("
                CREATE VIEW IF NOT EXISTS admin_customer_view AS
                SELECT 
                    p.id AS id,
                    (p.display_name || '@example.com') AS email,
                    p.display_name AS display_name,
                    p.role AS role,
                    p.created_at AS created_at,
                    p.updated_at AS updated_at
                FROM profiles p;
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS admin_customer_view;");
    }
};
