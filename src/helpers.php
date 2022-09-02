<?php

use Hyperf\Utils\Str;

if (!function_exists('generateRandomCode')) {
    function createCodeForGivenName(?string $name='', ?array &$data=[], ?int $length = 16): array|string
    {
        if (!$name || !$data) {
            return Str::random($length);
        }
        if (empty($data["{$name}"])) {
            $data["{$name}"] = Str::random($length);
        }
        return $data;
    }
}
