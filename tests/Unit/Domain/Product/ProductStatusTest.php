<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Product;

use App\Domain\Product\ValueObjects\ProductStatus;
use PHPUnit\Framework\TestCase;

final class ProductStatusTest extends TestCase
{
    public function test_valid_values(): void
    {
        $active = new ProductStatus('active');
        $inactive = new ProductStatus('inactive');
        $this->assertSame('active', $active->value());
        $this->assertSame('inactive', $inactive->value());
        $this->assertTrue($active->isActive());
        $this->assertFalse($inactive->isActive());
    }

    public function test_invalid_value_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid status value');
        new ProductStatus('disabled');
    }
}
