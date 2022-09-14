<?php
declare(strict_types=1);
namespace Rickytech\Library\Traits;

use Hyperf\Utils\Str;

trait Helpers
{
    public function createCodeForGivenName(?string $name='', ?array &$data=[], ?int $length = 16): array|string
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
