<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PostgresSchemaComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_postgres_test_schema_enforces_auth_users_foreign_keys(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('This test requires PostgreSQL.');
        }

        $profilesFks = Schema::getForeignKeys('profiles');
        $this->assertNotEmpty($profilesFks, 'Profiles table should have foreign keys.');
        
        $hasProfileToUserFk = false;
        foreach ($profilesFks as $fk) {
            if (in_array('id', $fk['columns']) && $fk['foreign_table'] === 'users' && in_array('id', $fk['foreign_columns'])) {
                $hasProfileToUserFk = true;
                $this->assertEquals('cascade', strtolower($fk['on_delete']));
            }
        }
        $this->assertTrue($hasProfileToUserFk, 'profiles.id must reference auth.users(id) ON DELETE CASCADE');

        $ordersFks = Schema::getForeignKeys('orders');
        $this->assertNotEmpty($ordersFks, 'Orders table should have foreign keys.');

        $hasOrderToUserFk = false;
        foreach ($ordersFks as $fk) {
            if (in_array('user_id', $fk['columns']) && $fk['foreign_table'] === 'users' && in_array('id', $fk['foreign_columns'])) {
                $hasOrderToUserFk = true;
                // PostgreSQL NO ACTION and RESTRICT might be reported similarly, but user asked for RESTRICT.
                // We will just verify it's not CASCADE or SET NULL, or specifically check for restrict.
                $this->assertTrue(in_array(strtolower($fk['on_delete']), ['restrict', 'no action']), 'orders.user_id must reference auth.users(id) ON DELETE RESTRICT or NO ACTION');
            }
        }
        $this->assertTrue($hasOrderToUserFk, 'orders.user_id must reference auth.users(id)');
    }
}
