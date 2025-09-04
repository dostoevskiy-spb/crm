<?php

declare(strict_types=1);

namespace App\Modules\LegalEntity\Tests\Unit\Application\Handler;

use App\Modules\LegalEntity\Application\Command\CreateLegalEntityCommand;
use App\Modules\LegalEntity\Application\DTO\CreateLegalEntityDTO;
use App\Modules\LegalEntity\Application\Handler\CreateLegalEntityHandler;
use App\Modules\LegalEntity\Domain\Contracts\LegalEntityRepositoryInterface;
use App\Modules\LegalEntity\Domain\Models\LegalEntity;
use PHPUnit\Framework\TestCase;

final class CreateLegalEntityHandlerTest extends TestCase
{
    public function test_can_create_legal_entity(): void
    {
        $repository = $this->createMock(LegalEntityRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('existsByInn')
            ->with('7701870742')
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
            ogrn: '1107746232593',
            inn: '7701870742',
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
