<?php

declare(strict_types=1);

namespace Tests\Unit\Application\LegalEntity\Handler;

use App\Application\LegalEntity\Command\CreateLegalEntityCommand;
use App\Application\LegalEntity\DTO\CreateLegalEntityDTO;
use App\Application\LegalEntity\Handler\CreateLegalEntityHandler;
use App\Domain\LegalEntity\Contracts\LegalEntityRepositoryInterface;
use App\Domain\LegalEntity\Models\LegalEntity;
use PHPUnit\Framework\TestCase;

final class CreateLegalEntityHandlerTest extends TestCase
{
    public function test_can_create_legal_entity(): void
    {
        $repository = $this->createMock(LegalEntityRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('existsByInn')
            ->with('1234567890')
            ->willReturn(false);

        $repository->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (LegalEntity $entity) {
                return $entity;
            });

        $handler = new CreateLegalEntityHandler($repository);

        $dto = new CreateLegalEntityDTO(
            shortName: 'ООО "Тест"',
            fullName: 'Общество с ограниченной ответственностью "Тест"',
            ogrn: '1234567890123',
            inn: '1234567890',
            kpp: '123456789',
            legalAddress: '123456, г. Москва, ул. Тестовая, д. 1',
            phoneNumber: '+7 495 123-45-67',
            email: 'test@example.com'
        );

        $command = new CreateLegalEntityCommand($dto);
        $uid = $handler($command);

        $this->assertIsString($uid);
        $this->assertNotEmpty($uid);
    }

    public function test_throws_exception_when_inn_already_exists(): void
    {
        $repository = $this->createMock(LegalEntityRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('existsByInn')
            ->with('1234567890')
            ->willReturn(true);

        $repository->expects($this->never())
            ->method('save');

        $handler = new CreateLegalEntityHandler($repository);

        $dto = new CreateLegalEntityDTO(
            shortName: 'ООО "Тест"',
            fullName: 'Общество с ограниченной ответственностью "Тест"',
            ogrn: '1234567890123',
            inn: '1234567890',
            kpp: '123456789'
        );

        $command = new CreateLegalEntityCommand($dto);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Legal entity with this INN already exists');

        $handler($command);
    }
}
