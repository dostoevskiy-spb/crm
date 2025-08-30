<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Product;

use App\Domain\Product\ValueObjects\ProductName;
use PHPUnit\Framework\TestCase;

final class ProductNameTest extends TestCase
{
    public function test_valid_name(): void
    {
        $vo = new ProductName('Hammer');
        $this->assertSame('Hammer', $vo->value());
        $this->assertSame('Hammer', (string) $vo);
    }

    public function test_empty_or_too_long_name_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name must be between 1 and 50 characters');
        new ProductName('');
    }

    public function test_too_long_name_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name must be between 1 and 50 characters');
        new ProductName(str_repeat('a', 51));
    }
}
