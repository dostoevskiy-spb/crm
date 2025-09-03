<?php

declare(strict_types=1);

namespace App\Modules\Individual\Domain\Contracts;

use App\Modules\Individual\Domain\Enums\StatusEnum;
use App\Modules\Individual\Domain\Models\Individual;
use App\Modules\Individual\Domain\ValueObjects\Id;
use App\Modules\Individual\Domain\ValueObjects\Login;

interface IndividualRepositoryInterface
{
    public function findByUid(Id $uid): ?Individual;

    public function findByLogin(Login $login): ?Individual;

    public function findAll(): array;

    public function findByFilters(array $filters): array;

    public function save(Individual $person): Individual;

    public function delete(Id $uid): bool;

    public function existsByLogin(Login $login): bool;

    public function findCompanyEmployees(): array;

    public function findByCreator(Id $creatorUid): array;

    public function findByStatus(StatusEnum $status): array;
}
