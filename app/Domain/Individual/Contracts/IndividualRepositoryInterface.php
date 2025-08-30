<?php

declare(strict_types=1);

namespace App\Domain\Individual\Contracts;

use App\Domain\Individual\Models\Individual;
use App\Domain\Individual\ValueObjects\Login;
use App\Domain\Individual\ValueObjects\PersonStatus;
use App\Domain\Individual\ValueObjects\PersonUid;

interface IndividualRepositoryInterface
{
    public function findByUid(PersonUid $uid): ?Individual;

    public function findByLogin(Login $login): ?Individual;

    public function findAll(): array;

    public function findByFilters(array $filters): array;

    public function save(Individual $person): Individual;

    public function delete(PersonUid $uid): bool;

    public function existsByLogin(Login $login): bool;

    public function findCompanyEmployees(): array;

    public function findByCreator(PersonUid $creatorUid): array;

    public function findByStatus(PersonStatus $status): array;
}
