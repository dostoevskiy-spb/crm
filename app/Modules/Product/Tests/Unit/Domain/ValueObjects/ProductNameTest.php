<?php

declare(strict_types=1);

namespace App\Modules\Product\Tests\Unit\Domain\ValueObjects;

use App\Modules\Product\Domain\ValueObjects\Name;
use PHPUnit\Framework\TestCase;

final class ProductNameTest extends TestCase
{
    public function test_valid_name(): void
    {
        $vo = new Name('Hammer');
        $this->assertSame('Hammer', $vo->value());
        $this->assertSame('Hammer', (string) $vo);
    }

    public function test_empty_or_too_long_name_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name must be between 1 and 50 characters');
        new Name('');
    }

    public function test_too_long_name_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name must be between 1 and 50 characters');
        new Name(str_repeat('a', 51));
    }
}
