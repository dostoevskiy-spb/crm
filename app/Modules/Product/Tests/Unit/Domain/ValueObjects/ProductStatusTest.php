<?php

declare(strict_types=1);

namespace App\Modules\Product\Tests\Unit\Domain\ValueObjects;

use App\Modules\Product\Domain\Enum\StatusEnum;
use PHPUnit\Framework\TestCase;

final class ProductStatusTest extends TestCase
{
    public function test_valid_values(): void
    {
        $this->assertSame('active', StatusEnum::ACTIVE->value);
        $this->assertSame('inactive', StatusEnum::INACTIVE->value);
        $this->assertTrue(StatusEnum::ACTIVE->isActive());
        $this->assertFalse(StatusEnum::INACTIVE->isActive());
    }
}
