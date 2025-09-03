<?php

declare(strict_types=1);

namespace App\Modules\Individual\Application\Handler;

use App\Modules\Individual\Application\Query\GetIndividualQuery;
use App\Modules\Individual\Domain\Contracts\IndividualRepositoryInterface;
use App\Modules\Individual\Domain\ValueObjects\Id;

final class GetIndividualHandler
{
    public function __construct(
        private IndividualRepositoryInterface $repository
    ) {}

    public function __invoke(GetIndividualQuery $query): ?array
    {
        $uid = new Id($query->uid);
        $individual = $this->repository->findByUid($uid);

        if (! $individual) {
            return null;
        }

        return [
            'uid' => $individual->uid()->value(),
            'first_name' => $individual->getFirstName(),
            'last_name' => $individual->getLastName(),
            'middle_name' => $individual->getMiddleName(),
            'full_name' => $individual->getFullName(),
            'short_name' => $individual->getShortName(),
            'status' => match ($individual->statusId()) {
                1 => 'active',
                2 => 'archived',
                default => 'active',
            },
            'position_id' => $individual->positionId(),
            'login' => $individual->getLogin(),
            'is_company_employee' => $individual->isCompanyEmployee(),
            'creator_uid' => $individual->creatorUid()?->value(),
            'created_at' => $individual->createdAt()->toISOString(),
        ];
    }
}
