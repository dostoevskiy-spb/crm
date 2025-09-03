<?php

declare(strict_types=1);

namespace App\Modules\LegalEntity\Domain\Contracts;

use App\Modules\Individual\Domain\ValueObjects\Id;
use App\Modules\LegalEntity\Domain\Models\LegalEntity;
use App\Modules\LegalEntity\Domain\ValueObjects\Id;

interface LegalEntityRepositoryInterface
{
    public function findByUid(Id $uid): ?LegalEntity;

    public function findByInn(string $inn): ?LegalEntity;

    public function findAll(): array;

    public function findByFilters(array $filters): array;

    public function save(LegalEntity $legalEntity): LegalEntity;

    public function delete(Id $uid): bool;

    public function existsByInn(string $inn): bool;

    public function findByCurator(Id $curatorUid): array;

    public function findByCreator(Id $creatorUid): array;
}
