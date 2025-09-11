<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ApiKeyEncryptionService
{
    private string $encryptionKey;

    public function __construct()
    {
        $this->encryptionKey = config('app.api_key_encryption_key') ?: config('app.key');
        
        if (empty($this->encryptionKey)) {
            throw new InvalidArgumentException('API key encryption key not configured');
        }
    }

    /**
     * Generate a new encrypted API key
     */
    public function generateApiKey(string $prefix = 'sk'): array
    {
        $plainKey = $prefix . '_' . Str::random(32);
        $hashedKey = hash('sha256', $plainKey);
        $encryptedKey = Crypt::encryptString($plainKey);

        return [
            'plain_key' => $plainKey,
            'hashed_key' => $hashedKey,
            'encrypted_key' => $encryptedKey,
        ];
    }

    /**
     * Encrypt an existing API key
     */
    public function encryptApiKey(string $plainKey): string
    {
        return Crypt::encryptString($plainKey);
    }

    /**
     * Decrypt an encrypted API key
     */
    public function decryptApiKey(string $encryptedKey): string
    {
        try {
            return Crypt::decryptString($encryptedKey);
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid encrypted API key: ' . $e->getMessage());
        }
    }

    /**
     * Hash an API key for database storage
     */
    public function hashApiKey(string $plainKey): string
    {
        return hash('sha256', $plainKey);
    }

    /**
     * Verify API key against stored hash
     */
    public function verifyApiKey(string $plainKey, string $hashedKey): bool
    {
        return hash_equals($hashedKey, $this->hashApiKey($plainKey));
    }

    /**
     * Generate API key with expiration
     */
    public function generateExpiringApiKey(string $prefix = 'sk', int $expiresInDays = 365): array
    {
        $keyData = $this->generateApiKey($prefix);
        $expiresAt = now()->addDays($expiresInDays);

        return array_merge($keyData, [
            'expires_at' => $expiresAt,
            'expires_timestamp' => $expiresAt->timestamp,
        ]);
    }

    /**
     * Create a secure token for one-time operations
     */
    public function generateSecureToken(int $length = 32): string
    {
        return Str::random($length);
    }

    /**
     * Mask API key for display purposes
     */
    public function maskApiKey(string $plainKey): string
    {
        if (strlen($plainKey) <= 8) {
            return str_repeat('*', strlen($plainKey));
        }

        $prefix = substr($plainKey, 0, 4);
        $suffix = substr($plainKey, -4);
        $middle = str_repeat('*', strlen($plainKey) - 8);

        return $prefix . $middle . $suffix;
    }
}