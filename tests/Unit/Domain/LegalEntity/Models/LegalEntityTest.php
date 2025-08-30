<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\LegalEntity\Models;

use App\Domain\Individual\ValueObjects\PersonUid;
use App\Domain\LegalEntity\Models\LegalEntity;
use App\Domain\LegalEntity\ValueObjects\CompanyName;
use App\Domain\LegalEntity\ValueObjects\LegalEntityUid;
use App\Domain\LegalEntity\ValueObjects\TaxNumber;
use PHPUnit\Framework\TestCase;

final class LegalEntityTest extends TestCase
{
    public function test_can_create_legal_entity(): void
    {
        $name = new CompanyName('ООО "Тест"', 'Общество с ограниченной ответственностью "Тест"');
        $taxNumber = new TaxNumber('1234567890123', '1234567890', '123456789');
        $creatorUid = new PersonUid('550e8400-e29b-41d4-a716-446655440000');

        $legalEntity = new LegalEntity($name, $taxNumber, $creatorUid);

        $this->assertEquals('ООО "Тест"', $legalEntity->name()->shortName());
        $this->assertEquals('Общество с ограниченной ответственностью "Тест"', $legalEntity->name()->fullName());
        $this->assertEquals('1234567890123', $legalEntity->taxNumber()->ogrn());
        $this->assertEquals('1234567890', $legalEntity->taxNumber()->inn());
        $this->assertEquals('123456789', $legalEntity->taxNumber()->kpp());
        $this->assertEquals($creatorUid->value(), $legalEntity->creatorUid()->value());
        $this->assertInstanceOf(\DateTimeImmutable::class, $legalEntity->createdAt());
        $this->assertInstanceOf(LegalEntityUid::class, $legalEntity->uid());
    }

    public function test_can_set_optional_fields(): void
    {
        $name = new CompanyName('ООО "Тест"', 'Общество с ограниченной ответственностью "Тест"');
        $taxNumber = new TaxNumber('1234567890123', '1234567890', '123456789');

        $legalEntity = new LegalEntity($name, $taxNumber);

        $legalEntity->setLegalAddress('123456, г. Москва, ул. Тестовая, д. 1');
        $legalEntity->setPhoneNumber('+7 495 123-45-67');
        $legalEntity->setEmail('test@example.com');

        $curatorUid = new PersonUid('550e8400-e29b-41d4-a716-446655440001');
        $legalEntity->setCuratorUid($curatorUid);

        $this->assertEquals('123456, г. Москва, ул. Тестовая, д. 1', $legalEntity->legalAddress());
        $this->assertEquals('+7 495 123-45-67', $legalEntity->phoneNumber());
        $this->assertEquals('test@example.com', $legalEntity->email());
        $this->assertEquals($curatorUid->value(), $legalEntity->curatorUid()->value());
    }
}
