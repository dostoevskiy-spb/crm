<?php

declare(strict_types=1);

namespace App\Modules\Product\Tests\Unit\Domain\ValueObjects;

use App\Modules\Product\Domain\ValueObjects\Type;
use PHPUnit\Framework\TestCase;

final class ProductTypeTest extends TestCase
{
    public function test_valid_values(): void
    {
        $item = new Type('item');
        $service = new Type('service');
        $this->assertSame('item', $item->value());
        $this->assertSame('service', $service->value());
        $this->assertTrue($item->isItem());
        $this->assertTrue($service->isService());
    }

    public function test_invalid_value_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid product type');
        new Type('goods');
    }
}
