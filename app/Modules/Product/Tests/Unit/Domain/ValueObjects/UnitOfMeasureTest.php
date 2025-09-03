<?php

declare(strict_types=1);

namespace App\Modules\Product\Tests\Unit\Domain\ValueObjects;

use App\Modules\Product\Domain\ValueObjects\UnitOfMeasure;
use PHPUnit\Framework\TestCase;

final class UnitOfMeasureTest extends TestCase
{
    public function test_valid_unit(): void
    {
        $vo = new UnitOfMeasure('pcs');
        $this->assertSame('pcs', $vo->value());
        $this->assertSame('pcs', (string) $vo);
    }

    public function test_empty_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unit of measure must be between 1 and 20 characters');
        new UnitOfMeasure('');
    }

    public function test_too_long_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unit of measure must be between 1 and 20 characters');
        new UnitOfMeasure(str_repeat('a', 21));
    }
}
