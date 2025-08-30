<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Product;

use App\Domain\Product\ValueObjects\ProductType;
use PHPUnit\Framework\TestCase;

final class ProductTypeTest extends TestCase
{
    public function test_valid_values(): void
    {
        $item = new ProductType('item');
        $service = new ProductType('service');
        $this->assertSame('item', $item->value());
        $this->assertSame('service', $service->value());
        $this->assertTrue($item->isItem());
        $this->assertTrue($service->isService());
    }

    public function test_invalid_value_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid product type');
        new ProductType('goods');
    }
}
