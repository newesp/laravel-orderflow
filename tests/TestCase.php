<?php

namespace Tests;

use App\Models\Profile;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Profile::creating(function ($profile) {
            if (DB::getDriverName() !== 'pgsql') return;
            DB::table('auth.users')->insertOrIgnore([
                'id' => $profile->id,
                'email' => $profile->id . '@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }
}

