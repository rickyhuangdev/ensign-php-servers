<?php

declare(strict_types=1);

namespace Rickytech\Library\Services\Helpers;

use Hyperf\Utils\Str;

class StringHelper
{
    /**
     * Format cache key with prefix and arguments.
     */
    public static function format(string $prefix, array $arguments, ?string $value = null): string
    {
        if ($value !== null) {
            if ($matches = self::parse($value)) {
                foreach ($matches as $search) {
                    $k = str_replace(['#{', '}'], '', $search);

                    $value = Str::replaceFirst($search, (string)data_get($arguments, $k), $value);
                }
            }
        } else {
            $value = implode(':', $arguments);
        }

        return $prefix . ':' . $value;
    }

    /**
     * Parse expression of value.
     */
    public static function parse(string $value): array
    {
        preg_match_all('/\#\{[\w\.]+\}/', $value, $matches);

        return $matches[0] ?? [];
    }

    public static function removeSpecialCharacter(string $string): array|string
    {
        $t = $string;

        $specChars = array(
            ' '     => '-',
            '!'     => '',
            '"'     => '',
            '#'     => '',
            '$'     => '',
            '%'     => '',
            '&'     => '',
            '\''    => '',
            '('     => '',
            ')'     => '',
            '*'     => '',
            '+'     => '',
            ','     => '',
            '₹'     => '',
            '.'     => '',
            '/-'    => '',
            ':'     => '',
            ';'     => '',
            '<'     => '',
            '='     => '',
            '>'     => '',
            '?'     => '',
            '@'     => '',
            '['     => '',
            '\\'    => '',
            ']'     => '',
            '^'     => '',
            '_'     => '',
            '`'     => '',
            '{'     => '',
            '|'     => '',
            '}'     => '',
            '~'     => '',
            '-----' => '-',
            '----'  => '-',
            '---'   => '-',
            '/'     => '',
            '--'    => '-',
            '/_'    => '-',

        );

        foreach ($specChars as $k => $v) {
            $t = str_replace($k, $v, $t);
        }

        return $t;
    }
}
