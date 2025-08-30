<?php

declare(strict_types=1);

namespace App\Application\Individual\Handler;

use App\Application\Individual\Query\GetIndividualQuery;
use App\Domain\Individual\Contracts\IndividualRepositoryInterface;
use App\Domain\Individual\ValueObjects\PersonUid;

final class GetIndividualHandler
{
    public function __construct(
        private IndividualRepositoryInterface $repository
    ) {}

    public function __invoke(GetIndividualQuery $query): ?array
    {
        $uid = new PersonUid($query->uid);
        $individual = $this->repository->findByUid($uid);

        if (!$individual) {
            return null;
        }

        return [
            'uid' => $individual->uid()->value(),
            'first_name' => $individual->getFirstName(),
            'last_name' => $individual->getLastName(),
            'middle_name' => $individual->getMiddleName(),
            'full_name' => $individual->getFullName(),
            'short_name' => $individual->getShortName(),
            'status_id' => $individual->getStatusId(),
            'position_id' => $individual->positionId(),
            'login' => $individual->getLogin(),
            'is_company_employee' => $individual->isCompanyEmployee(),
            'creator_uid' => $individual->creatorUid()?->value(),
            'created_at' => $individual->createdAt()->toISOString(),
        ];
    }
}
