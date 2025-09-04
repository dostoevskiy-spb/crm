<?php

declare(strict_types=1);

namespace App\Modules\LegalEntity\Tests\Unit\Domain\ValueObjects;

use App\Modules\LegalEntity\Domain\ValueObjects\Name;
use PHPUnit\Framework\TestCase;

final class NameTest extends TestCase
{
    public function test_can_create_valid_company_name(): void
    {
        $companyName = new Name('ООО "Тест"', 'Общество с ограниченной ответственностью "Тест"');

        $this->assertEquals('ООО "Тест"', $companyName->shortName());
        $this->assertEquals('Общество с ограниченной ответственностью "Тест"', $companyName->fullName());
        $this->assertEquals('ООО "Тест"', (string) $companyName);
    }

    public function test_throws_exception_for_empty_short_name(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Short name must be between 1 and 20 characters');

        new Name('', 'Общество с ограниченной ответственностью "Тест"');
    }

    public function test_throws_exception_for_too_long_short_name(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Short name must be between 1 and 20 characters');

        new Name(str_repeat('a', 21), 'Общество с ограниченной ответственностью "Тест"');
    }

    public function test_throws_exception_for_empty_full_name(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Full name must be between 1 and 255 characters');

        new Name('ООО "Тест"', '');
    }

    public function test_throws_exception_for_too_long_full_name(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Full name must be between 1 and 255 characters');

        new Name('ООО "Тест"', str_repeat('a', 256));
    }
}
