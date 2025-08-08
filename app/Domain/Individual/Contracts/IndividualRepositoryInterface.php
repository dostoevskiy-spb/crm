<?php

declare(strict_types=1);

namespace App\Domain\Individual\Contracts;

use App\Domain\Individual\Models\Individual;

interface IndividualRepositoryInterface
{
    public function findById(int $id): ?Individual;

    public function findByLogin(string $login): ?Individual;

    public function findAll(): array;

    public function findByFilters(array $filters): array;

    public function save(Individual $person): Individual;

    public function delete(int $id): bool;

    public function existsByLogin(string $login): bool;

    public function findCompanyEmployees(): array;

    public function findByCreator(int $creatorId): array;

    public function findByStatus(int $statusId): array;
}
