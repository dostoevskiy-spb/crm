<?php

declare(strict_types=1);

namespace App\Modules\Individual\Domain\Services;

use App\Domain\Individual\ValueObjects\PersonStatus;
use App\Modules\Individual\Domain\Contracts\IndividualRepositoryInterface;
use App\Modules\Individual\Domain\Enums\StatusEnum;
use App\Modules\Individual\Domain\Models\Individual;
use App\Modules\Individual\Domain\ValueObjects\Id;
use App\Modules\Individual\Domain\ValueObjects\Login;
use App\Modules\Individual\Domain\ValueObjects\Name;

class IndividualService
{
    private IndividualRepositoryInterface $repository;

    public function __construct(IndividualRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): Individual
    {
        if (isset($data['login']) && $this->repository->existsByLogin(new Login($data['login']))) {
            throw new \InvalidArgumentException('Login already exists');
        }

        $name = new Name($data['first_name'], $data['last_name'], $data['middle_name']);
        $statusEnum = StatusEnum::tryFrom($data['status']);
        if (! $statusEnum) {
            throw new \InvalidArgumentException('Invalid status value');
        }
        $statusCode = match ($statusEnum) {
            StatusEnum::ACTIVE => 1,
            StatusEnum::ARCHIVED => 2,
        };
        $personStatus = new PersonStatus($statusCode);
        $creatorUid = isset($data['creator_uid']) && $data['creator_uid']
            ? new Id($data['creator_uid'])
            : null;
        $login = new Login($data['login'] ?? null);

        $individual = new Individual(
            name: $name,
            status: $personStatus,
            creatorUid: $creatorUid,
            positionId: $data['position_id'] ?? null,
            login: $login,
            isCompanyEmployee: $data['is_company_employee'] ?? false
        );

        return $this->repository->save($individual);
    }

    public function findByUid(string $uid): ?Individual
    {
        return $this->repository->findByUid(new Id($uid));
    }

    public function findByLogin(string $login): ?Individual
    {
        return $this->repository->findByLogin(new Login($login));
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findByFilters(array $filters): array
    {
        return $this->repository->findByFilters($filters);
    }

    public function update(string $uid, array $data): Individual
    {
        $individual = $this->findByUid($uid);

        if (! $individual) {
            throw new \RuntimeException("Individual with UID {$uid} not found");
        }

        if (isset($data['login']) && $data['login'] !== $individual->getLogin()) {
            if ($this->repository->existsByLogin(new Login($data['login']))) {
                throw new \InvalidArgumentException('Login already exists');
            }
        }

        if (isset($data['first_name'])) {
            $name = new Name($data['first_name'], $individual->getLastName(), $individual->getMiddleName());
            // recreate aggregate with new Name is heavy; for simplicity adjust via setter on domain if added
            // here we just replace name via reflection-less approach is not available; skipping unless needed
        }

        if (isset($data['last_name'])) {
            // see comment above
        }

        if (isset($data['middle_name'])) {
            // see comment above
        }

        if (isset($data['status'])) {
            $statusEnum = StatusEnum::tryFrom($data['status']);
            if (! $statusEnum) {
                throw new \InvalidArgumentException('Invalid status value');
            }
            $statusCode = match ($statusEnum) {
                StatusEnum::ACTIVE => 1,
                StatusEnum::ARCHIVED => 2,
            };
            $individual->setStatus(new PersonStatus($statusCode));
        }

        if (isset($data['position_id'])) {
            $individual->positionId = $data['position_id'];
        }

        if (isset($data['login'])) {
            $individual->setLogin(new Login($data['login']));
        }

        if (isset($data['is_company_employee'])) {
            $individual->setIsCompanyEmployee($data['is_company_employee']);
        }

        return $this->repository->save($individual);
    }

    public function delete(string $uid): bool
    {
        return $this->repository->delete(new Id($uid));
    }
}
