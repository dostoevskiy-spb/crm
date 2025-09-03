<?php

declare(strict_types=1);

namespace App\Modules\Product\Tests\Unit\Domain\ValueObjects;

use App\Modules\Product\Domain\ValueObjects\Price;
use PHPUnit\Framework\TestCase;

final class ProductPriceTest extends TestCase
{
    public function test_normalizes_to_two_decimals_and_string(): void
    {
        $price = new Price('10');
        $this->assertSame('10.00', $price->value());
        $this->assertSame('10.00', (string) $price);

        $price2 = new Price('10,5');
        $this->assertSame('10.50', $price2->value());

        $price3 = new Price(12.345);
        $this->assertSame('12.35', $price3->value());
    }

    public function test_negative_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Price must be non-negative');
        new Price('-1');
    }
}
