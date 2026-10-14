<?php
namespace App\Services;

final class ExactIdrAmount
{
    public static function matches(mixed $providerAmount, int $expectedRupiah): bool
    {
        if ($expectedRupiah < 0 || (!is_string($providerAmount) && !is_int($providerAmount))) {
            return false;
        }

        $value = (string) $providerAmount;
        if (!preg_match('/^(0|[1-9][0-9]*)(?:\.0{1,2})?$/D', $value)) {
            return false;
        }

        $whole = explode('.', $value, 2)[0];
        return $whole === (string) $expectedRupiah;
    }
}
