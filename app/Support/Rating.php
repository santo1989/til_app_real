<?php

namespace App\Support;

class Rating
{
    /**
     * Convert a human-friendly label to the DB token using config/rating.php
     *
     * @param string $label
     * @return string
     */
    public static function toDbToken(string $label): string
    {
        $map = config('rating.map', []);
        $key = trim($label);
        if (isset($map[$key])) {
            return $map[$key];
        }

        $lower = strtolower($key);
        foreach ($map as $k => $v) {
            if (strtolower($k) === $lower) {
                return $v;
            }
        }

        return config('rating.default_db_token', 'average');
    }

    /**
     * Convert a DB token to the preferred display label
     *
     * @param string $token
     * @return string
     */
    public static function toDisplayLabel(string $token): string
    {
        $display = config('rating.display', []);
        return $display[$token] ?? ucfirst($token);
    }
}
