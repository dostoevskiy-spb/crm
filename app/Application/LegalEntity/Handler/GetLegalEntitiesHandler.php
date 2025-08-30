<?php

declare(strict_types=1);

namespace App\Application\LegalEntity\Handler;

use App\Application\LegalEntity\Query\GetLegalEntitiesQuery;
use App\Domain\LegalEntity\Contracts\LegalEntityRepositoryInterface;

final readonly class GetLegalEntitiesHandler
{
    public function __construct(
        private LegalEntityRepositoryInterface $legalEntityRepository
    ) {}

    public function __invoke(GetLegalEntitiesQuery $query): array
    {
        $legalEntities = $this->legalEntityRepository->findByFilters($query->filters);

        return array_map(function ($legalEntity) {
            return [
                'uid' => $legalEntity->uid()->value(),
                'shortName' => $legalEntity->name()->shortName(),
                'fullName' => $legalEntity->name()->fullName(),
                'inn' => $legalEntity->taxNumber()->inn(),
                'phoneNumber' => $legalEntity->phoneNumber(),
                'email' => $legalEntity->email(),
                'createdAt' => $legalEntity->createdAt()->format('Y-m-d H:i:s'),
                'creatorUid' => $legalEntity->creatorUid()?->value(),
                'curatorUid' => $legalEntity->curatorUid()?->value(),
            ];
        }, $legalEntities);
    }
}
