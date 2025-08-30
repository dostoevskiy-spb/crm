<?php

declare(strict_types=1);

namespace App\Application\LegalEntity\Handler;

use App\Application\LegalEntity\Query\GetLegalEntityQuery;
use App\Domain\LegalEntity\Contracts\LegalEntityRepositoryInterface;
use App\Domain\LegalEntity\ValueObjects\LegalEntityUid;

final class GetLegalEntityHandler
{
    public function __construct(
        private LegalEntityRepositoryInterface $legalEntityRepository
    ) {}

    public function __invoke(GetLegalEntityQuery $query): ?array
    {
        $uid = new LegalEntityUid($query->uid);
        $legalEntity = $this->legalEntityRepository->findByUid($uid);

        if (!$legalEntity) {
            return null;
        }

        return [
            'uid' => $legalEntity->uid()->value(),
            'shortName' => $legalEntity->name()->shortName(),
            'fullName' => $legalEntity->name()->fullName(),
            'ogrn' => $legalEntity->taxNumber()->ogrn(),
            'inn' => $legalEntity->taxNumber()->inn(),
            'kpp' => $legalEntity->taxNumber()->kpp(),
            'legalAddress' => $legalEntity->legalAddress(),
            'phoneNumber' => $legalEntity->phoneNumber(),
            'email' => $legalEntity->email(),
            'createdAt' => $legalEntity->createdAt()->format('Y-m-d H:i:s'),
            'creatorUid' => $legalEntity->creatorUid()?->value(),
            'curatorUid' => $legalEntity->curatorUid()?->value(),
        ];
    }
}
