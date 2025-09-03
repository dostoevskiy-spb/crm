<?php

declare(strict_types=1);

namespace App\Modules\LegalEntity\Tests\Unit\Domain\Models;

use App\Modules\Individual\Domain\ValueObjects\Id;
use App\Modules\LegalEntity\Domain\Models\LegalEntity;
use App\Modules\LegalEntity\Domain\ValueObjects\Name;
use App\Modules\LegalEntity\Domain\ValueObjects\Id;
use App\Modules\LegalEntity\Domain\ValueObjects\TaxNumber;
use PHPUnit\Framework\TestCase;

final class LegalEntityTest extends TestCase
{
    public function test_can_create_legal_entity(): void
    {
        $name = new Name('ООО "Тест"', 'Общество с ограниченной ответственностью "Тест"');
        $taxNumber = new TaxNumber('1234567890123', '1234567890', '123456789');
        $creatorUid = new Id('550e8400-e29b-41d4-a716-446655440000');

        $legalEntity = new LegalEntity($name, $taxNumber, $creatorUid);

        $this->assertEquals('ООО "Тест"', $legalEntity->name()->shortName());
        $this->assertEquals('Общество с ограниченной ответственностью "Тест"', $legalEntity->name()->fullName());
        $this->assertEquals('1234567890123', $legalEntity->taxNumber()->ogrn());
        $this->assertEquals('1234567890', $legalEntity->taxNumber()->inn());
        $this->assertEquals('123456789', $legalEntity->taxNumber()->kpp());
        $this->assertEquals($creatorUid->value(), $legalEntity->creatorUid()->value());
        $this->assertInstanceOf(\DateTimeImmutable::class, $legalEntity->createdAt());
        $this->assertInstanceOf(Id::class, $legalEntity->uid());
    }

    public function test_can_set_optional_fields(): void
    {
        $name = new Name('ООО "Тест"', 'Общество с ограниченной ответственностью "Тест"');
        $taxNumber = new TaxNumber('1234567890123', '1234567890', '123456789');

        $legalEntity = new LegalEntity($name, $taxNumber);

        $legalEntity->setLegalAddress('123456, г. Москва, ул. Тестовая, д. 1');
        $legalEntity->setPhoneNumber('+7 495 123-45-67');
        $legalEntity->setEmail('test@example.com');

        $curatorUid = new Id('550e8400-e29b-41d4-a716-446655440001');
        $legalEntity->setCuratorUid($curatorUid);

        $this->assertEquals('123456, г. Москва, ул. Тестовая, д. 1', $legalEntity->legalAddress());
        $this->assertEquals('+7 495 123-45-67', $legalEntity->phoneNumber());
        $this->assertEquals('test@example.com', $legalEntity->email());
        $this->assertEquals($curatorUid->value(), $legalEntity->curatorUid()->value());
    }
}
