<?php

declare(strict_types=1);

namespace App\Modules\Individual\Application\Handler;

use App\Modules\Individual\Application\Query\GetIndividualsQuery;
use App\Modules\Individual\Domain\Contracts\IndividualRepositoryInterface;

final readonly class GetIndividualsHandler
{
    public function __construct(
        private IndividualRepositoryInterface $repository
    ) {}

    public function __invoke(GetIndividualsQuery $query): array
    {
        $individuals = empty($query->filters)
            ? $this->repository->findAll()
            : $this->repository->findByFilters($query->filters);

        return array_map(function ($individual) {
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
        }, $individuals);
    }
}

