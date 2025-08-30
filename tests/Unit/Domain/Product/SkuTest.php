<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Product;

use App\Domain\Product\ValueObjects\Sku;
use PHPUnit\Framework\TestCase;

final class SkuTest extends TestCase
{
    public function test_valid_sku(): void
    {
        $sku = new Sku('SKU-001');
        $this->assertSame('SKU-001', $sku->value());
        $this->assertSame('SKU-001', (string) $sku);
    }

    public function test_empty_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SKU must be between 1 and 50 characters');
        new Sku('');
    }

    public function test_too_long_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SKU must be between 1 and 50 characters');
        new Sku(str_repeat('a', 51));
    }
}
