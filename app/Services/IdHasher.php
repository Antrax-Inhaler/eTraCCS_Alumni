<?php

namespace App\Services;

use Hashids\Hashids;
use Illuminate\Support\Facades\Crypt;

class IdHasher
{
    protected $hashids;

    public function __construct()
    {
        $this->hashids = new Hashids(config('app.key'), 8); // Minimum length 8
    }

    // For public/non-sensitive routes
    public function encodeId($id)
    {
        return $this->hashids->encode($id);
    }

    public function decodeId($hash)
    {
        $result = $this->hashids->decode($hash);
        return $result[0] ?? null;
    }

    // For sensitive routes (combines Hashids with encryption)
    public function secureEncode($id)
    {
        $hash = $this->encodeId($id);
        return Crypt::encryptString($hash);
    }

    public function secureDecode($encrypted)
    {
        try {
            $hash = Crypt::decryptString($encrypted);
            return $this->decodeId($hash);
        } catch (\Exception $e) {
            return null;
        }
    }
}