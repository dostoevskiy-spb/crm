<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\LegalEntity\ValueObjects;

use App\Domain\LegalEntity\ValueObjects\TaxNumber;
use PHPUnit\Framework\TestCase;

final class TaxNumberTest extends TestCase
{
    public function test_can_create_valid_tax_number(): void
    {
        $taxNumber = new TaxNumber('1234567890123', '1234567890', '123456789');

        $this->assertEquals('1234567890123', $taxNumber->ogrn());
        $this->assertEquals('1234567890', $taxNumber->inn());
        $this->assertEquals('123456789', $taxNumber->kpp());
    }

    public function test_throws_exception_for_invalid_ogrn(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('OGRN must contain exactly 13 digits');

        new TaxNumber('123456789012', '1234567890', '123456789');
    }

    public function test_throws_exception_for_non_numeric_ogrn(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('OGRN must contain exactly 13 digits');

        new TaxNumber('123456789012a', '1234567890', '123456789');
    }

    public function test_throws_exception_for_invalid_inn(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('INN must contain exactly 10 digits');

        new TaxNumber('1234567890123', '123456789', '123456789');
    }

    public function test_throws_exception_for_non_numeric_inn(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('INN must contain exactly 10 digits');

        new TaxNumber('1234567890123', '123456789a', '123456789');
    }

    public function test_throws_exception_for_invalid_kpp(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('KPP must contain exactly 9 digits');

        new TaxNumber('1234567890123', '1234567890', '12345678');
    }

    public function test_throws_exception_for_non_numeric_kpp(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('KPP must contain exactly 9 digits');

        new TaxNumber('1234567890123', '1234567890', '12345678a');
    }
}
