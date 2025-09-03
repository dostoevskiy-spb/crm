<?php

declare(strict_types=1);

namespace App\Modules\LegalEntity\Application\Handler;

use App\Modules\Individual\Domain\ValueObjects\Id;
use App\Modules\LegalEntity\Application\Command\CreateLegalEntityCommand;
use App\Modules\LegalEntity\Domain\Contracts\LegalEntityRepositoryInterface;
use App\Modules\LegalEntity\Domain\Models\LegalEntity;
use App\Modules\LegalEntity\Domain\ValueObjects\Name;
use App\Modules\LegalEntity\Domain\ValueObjects\TaxNumber;

final class CreateLegalEntityHandler
{
    public function __construct(
        private readonly LegalEntityRepositoryInterface $legalEntityRepository
    ) {}

    public function __invoke(CreateLegalEntityCommand $command): string
    {
        $dto = $command->dto;

        if ($this->legalEntityRepository->existsByInn($dto->inn)) {
            throw new \InvalidArgumentException('Legal entity with this INN already exists');
        }

        $name = new Name($dto->shortName, $dto->fullName);
        $taxNumber = new TaxNumber($dto->ogrn, $dto->inn, $dto->kpp);
        $creatorUid = $dto->creatorUid ? new Id($dto->creatorUid) : null;

        $legalEntity = new LegalEntity($name, $taxNumber, $creatorUid);

        if ($dto->legalAddress) {
            $legalEntity->setLegalAddress($dto->legalAddress);
        }

        if ($dto->phoneNumber) {
            $legalEntity->setPhoneNumber($dto->phoneNumber);
        }

        if ($dto->email) {
            $legalEntity->setEmail($dto->email);
        }

        $savedEntity = $this->legalEntityRepository->save($legalEntity);

        return $savedEntity->uid()->value();
    }
}
