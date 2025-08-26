<?php

namespace App\Services;

class SshaHashService
{
    /**
     * Generate an SSHA password hash.
     * The hash is returned without the {SSHA} prefix by default,
     * which is the format expected for storage in the radcheck table.
     *
     * @param  string  $password
     * @param  bool $withPrefix
     * @return string
     */
    public static function hash(string $password, bool $withPrefix = false): string
    {
        $salt = random_bytes(4);
        $hash = sha1($password . $salt, true);
        $ssha = base64_encode($hash . $salt);

        return $withPrefix ? '{SSHA}' . $ssha : $ssha;
    }

    /**
     * Verify a password against a stored SSHA hash.
     * This function can handle hashes with or without the {SSHA} prefix.
     * It uses hash_equals() for a timing-attack-safe comparison.
     *
     * @param  string  $password The plain text password.
     * @param  string  $hash The stored hash.
     * @return bool
     */
    public static function verify(string $password, string $hash): bool
    {
        if (strpos($hash, '{SSHA}') === 0) {
            $hash = substr($hash, 6);
        }

        $decoded = base64_decode($hash, true);

        // If base64 decoding fails or the string is not long enough, it's not a valid hash.
        if ($decoded === false || strlen($decoded) < 21) {
            return false;
        }

        $storedHash = substr($decoded, 0, 20);
        $salt = substr($decoded, 20);

        $calculatedHash = sha1($password . $salt, true);

        return hash_equals($calculatedHash, $storedHash);
    }
}
