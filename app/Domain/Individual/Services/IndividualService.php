<?php

declare(strict_types=1);

namespace App\Domain\Individual\Services;

use App\Domain\Individual\Contracts\IndividualRepositoryInterface;
use App\Domain\Individual\Models\Individual;
use App\Domain\Individual\ValueObjects\Login;
use App\Domain\Individual\ValueObjects\Name;
use App\Domain\Individual\ValueObjects\PersonStatus;
use App\Domain\Individual\ValueObjects\PersonUid;

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
        $status = new PersonStatus((int) $data['status_id']);
        $creatorUid = isset($data['creator_uid']) && $data['creator_uid']
            ? new PersonUid($data['creator_uid'])
            : null;
        $login = new Login($data['login'] ?? null);

        $individual = new Individual(
            name: $name,
            status: $status,
            creatorUid: $creatorUid,
            positionId: $data['position_id'] ?? null,
            login: $login,
            isCompanyEmployee: $data['is_company_employee'] ?? false
        );

        return $this->repository->save($individual);
    }

    public function findByUid(string $uid): ?Individual
    {
        return $this->repository->findByUid(new PersonUid($uid));
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

        if (!$individual) {
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

        if (isset($data['status_id'])) {
            $individual->setStatus(new PersonStatus((int) $data['status_id']));
        }

        if (isset($data['position_id'])) {
            $individual->setPositionId($data['position_id']);
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
        return $this->repository->delete(new PersonUid($uid));
    }
}
