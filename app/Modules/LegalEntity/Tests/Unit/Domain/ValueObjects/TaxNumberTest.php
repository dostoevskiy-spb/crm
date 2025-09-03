<?php

declare(strict_types=1);

namespace App\Modules\LegalEntity\Tests\Unit\Domain\ValueObjects;

use App\Modules\LegalEntity\Domain\ValueObjects\TaxNumber;
use PHPUnit\Framework\TestCase;

final class TaxNumberTest extends TestCase
{
    public function test_can_create_valid_tax_number_with_ogrn(): void
    {
        // Valid OGRN: 1027700132195 (Sberbank)
        // Check: 102770013219 % 11 = 5
        // Valid INN: 7707083893 (Sberbank)
        // Valid KPP: 773601001 (Moscow, tax office 36, reason 01)
        $taxNumber = new TaxNumber('1027700132195', '7707083893', '773601001');

        $this->assertEquals('1027700132195', $taxNumber->ogrn());
        $this->assertEquals('7707083893', $taxNumber->inn());
        $this->assertEquals('773601001', $taxNumber->kpp());
    }

    public function test_can_create_valid_tax_number_with_ogrnip(): void
    {
        // Valid OGRNIP example
        // 304500116000157: 30450011600015 % 13 = 7
        // Valid 12-digit INN: 500100732259
        // Valid KPP: 503201001 (Moscow region, tax office 32, reason 01)
        $taxNumber = new TaxNumber('304500116000157', '500100732259', '503201001');

        $this->assertEquals('304500116000157', $taxNumber->ogrn());
        $this->assertEquals('500100732259', $taxNumber->inn());
        $this->assertEquals('503201001', $taxNumber->kpp());
    }

    public function test_throws_exception_for_invalid_ogrn_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('OGRN must contain exactly 13 digits, or OGRNIP must contain exactly 15 digits');

        new TaxNumber('123456789012', '1234567890', '123456789');
    }

    public function test_throws_exception_for_invalid_ogrn_control_digit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid OGRN: control digit check failed');

        // Invalid OGRN: control digit should be 5, not 9
        new TaxNumber('1027700132199', '1234567890', '123456789');
    }

    public function test_throws_exception_for_invalid_ogrnip_control_digit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid OGRNIP: control digit check failed');

        // Invalid OGRNIP: control digit should be 7, not 9
        new TaxNumber('304500116000159', '1234567890', '123456789');
    }

    public function test_throws_exception_for_non_numeric_ogrn(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('OGRN must contain exactly 13 digits, or OGRNIP must contain exactly 15 digits');

        new TaxNumber('123456789012a', '1234567890', '123456789');
    }

    public function test_throws_exception_for_invalid_inn_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('INN must contain exactly 10 digits for legal entities or 12 digits for individuals/IP');

        // Using valid OGRN (1027700132195) but invalid INN length
        new TaxNumber('1027700132195', '123456789', '123456789');
    }

    public function test_throws_exception_for_invalid_inn_10_digit_control(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid INN: control digit check failed');

        // Invalid 10-digit INN: control digit should be 3, not 9
        new TaxNumber('1027700132195', '7707083899', '773601001');
    }

    public function test_throws_exception_for_invalid_inn_12_digit_control(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // Invalid 12-digit INN: wrong control digits
        new TaxNumber('304500116000157', '500100732250', '503201001');
    }

    public function test_throws_exception_for_non_numeric_inn(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('INN must contain exactly 10 digits for legal entities or 12 digits for individuals/IP');

        // Using valid OGRN (1027700132195) but invalid INN with letter
        new TaxNumber('1027700132195', '770708389a', '123456789');
    }

    public function test_throws_exception_for_invalid_kpp_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('KPP must contain exactly 9 digits');

        // Using valid OGRN and valid INN but invalid KPP length
        new TaxNumber('1027700132195', '7707083893', '12345678');
    }

    public function test_throws_exception_for_invalid_kpp_region_code(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid KPP: invalid region code');

        // Invalid region code 00 (must be 01-99, excluding some)
        new TaxNumber('1027700132195', '7707083893', '003601001');
    }

    public function test_throws_exception_for_invalid_kpp_reason_code(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid KPP: reason code must be between 01 and 99');

        // Invalid reason code 00 (must be 01-99)
        new TaxNumber('1027700132195', '7707083893', '773600001');
    }

    public function test_throws_exception_for_non_numeric_kpp(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('KPP must contain exactly 9 digits');

        // Using valid OGRN and valid INN but invalid KPP with letter
        new TaxNumber('1027700132195', '7707083893', '77360100a');
    }

    public function test_can_create_with_foreign_entity_kpp(): void
    {
        // Foreign entity KPP with reason code 51-99
        $taxNumber = new TaxNumber('1027700132195', '7707083893', '773651001');

        $this->assertEquals('773651001', $taxNumber->kpp());
    }
}
