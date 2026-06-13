<?php

namespace App\Services;

use App\Models\User;

/**
 * Thin wrapper around pragmarx/google2fa (Part 5 TOTP).
 *
 * NOTE: the google2fa package install is deferred until the network allows
 * (see [[dev-environment-quirks]]). These methods are only invoked once an
 * admin enables 2FA; the seeded admin has it disabled, so the login flow never
 * reaches here yet. When the package is installed this works as-is.
 */
class TwoFactorService
{
    public function isAvailable(): bool
    {
        return class_exists(\PragmaRX\Google2FA\Google2FA::class);
    }

    public function engine(): \PragmaRX\Google2FA\Google2FA
    {
        return new \PragmaRX\Google2FA\Google2FA();
    }

    public function generateSecret(): string
    {
        return $this->engine()->generateSecretKey();
    }

    public function verify(User $user, string $code): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }

        return $this->engine()->verifyKey($user->two_factor_secret, $code);
    }

    /** otpauth:// URI for QR-code provisioning. */
    public function provisioningUri(User $user): string
    {
        return $this->engine()->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_secret,
        );
    }
}
