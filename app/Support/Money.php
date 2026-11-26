<?php

namespace App\Support;

final class Money
{
    public static function normalize(string|int|float $amount): string
    {
        if (is_int($amount)) {
            return number_format($amount, 2, '.', '');
        }

        if (is_float($amount)) {
            return number_format($amount, 2, '.', '');
        }

        $amount = trim($amount);

        return bcadd($amount === '' ? '0' : $amount, '0', 2);
    }

    public static function add(string|int|float ...$amounts): string
    {
        $total = '0.00';

        foreach ($amounts as $amount) {
            $total = bcadd($total, self::normalize($amount), 2);
        }

        return $total;
    }

    public static function sub(string|int|float $minuend, string|int|float $subtrahend): string
    {
        return bcsub(self::normalize($minuend), self::normalize($subtrahend), 2);
    }

    public static function isNegative(string|int|float $amount): bool
    {
        return bccomp(self::normalize($amount), '0', 2) < 0;
    }
}
