<?php
namespace Tests\Unit;

use App\Services\ExactIdrAmount;
use PHPUnit\Framework\TestCase;

final class ExactIdrAmountTest extends TestCase
{
    public function test_exact_integer_amounts_match(): void
    {
        self::assertTrue(ExactIdrAmount::matches(1000, 1000));
        self::assertTrue(ExactIdrAmount::matches('1000', 1000));
        self::assertTrue(ExactIdrAmount::matches('1000.00', 1000));
    }

    public function test_fractional_or_ambiguous_amounts_never_match(): void
    {
        foreach (['1000.99', '1000.01', 1000.99, '1,000', '1e3', '01000', '-1000', ' 1000', null, true] as $value) {
            self::assertFalse(ExactIdrAmount::matches($value, 1000));
        }
        self::assertFalse(ExactIdrAmount::matches('1001', 1000));
    }
}
