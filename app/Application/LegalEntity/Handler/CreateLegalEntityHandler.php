<?php

declare(strict_types=1);

namespace App\Application\LegalEntity\Handler;

use App\Application\LegalEntity\Command\CreateLegalEntityCommand;
use App\Domain\Individual\ValueObjects\PersonUid;
use App\Domain\LegalEntity\Contracts\LegalEntityRepositoryInterface;
use App\Domain\LegalEntity\Models\LegalEntity;
use App\Domain\LegalEntity\ValueObjects\CompanyName;
use App\Domain\LegalEntity\ValueObjects\TaxNumber;

final class CreateLegalEntityHandler
{
    public function __construct(
        private LegalEntityRepositoryInterface $legalEntityRepository
    ) {}

    public function __invoke(CreateLegalEntityCommand $command): string
    {
        $dto = $command->dto;

        if ($this->legalEntityRepository->existsByInn($dto->inn)) {
            throw new \InvalidArgumentException('Legal entity with this INN already exists');
        }

        $name = new CompanyName($dto->shortName, $dto->fullName);
        $taxNumber = new TaxNumber($dto->ogrn, $dto->inn, $dto->kpp);
        $creatorUid = $dto->creatorUid ? new PersonUid($dto->creatorUid) : null;

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
