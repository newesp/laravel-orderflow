<?php

namespace App\Auth;

use App\Models\AdminSessionUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class AdminSessionUserProvider implements UserProvider
{
    /**
     * Retrieve a user by their unique identifier from session data.
     * Non-persistent: Does not query any database table.
     */
    public function retrieveById($identifier): ?AdminSessionUser
    {
        $data = session('admin_user');

        if (is_array($data) && !empty($data['id'])) {
            return AdminSessionUser::fromArray($data);
        }

        return null;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, $token): ?AdminSessionUser
    {
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // Non-persistent session, no-op
    }

    /**
     * Retrieve a user by the given credentials from session data.
     */
    public function retrieveByCredentials(array $credentials): ?AdminSessionUser
    {
        $data = session('admin_user');

        if (is_array($data) && !empty($data['id'])) {
            return AdminSessionUser::fromArray($data);
        }

        return null;
    }

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return true;
    }

    /**
     * Rehash the user's password if required and supported.
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): bool
    {
        return false;
    }
}
