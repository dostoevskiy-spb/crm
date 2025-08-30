<?php

declare(strict_types=1);

namespace App\Domain\LegalEntity\Contracts;

use App\Domain\Individual\ValueObjects\PersonUid;
use App\Domain\LegalEntity\Models\LegalEntity;
use App\Domain\LegalEntity\ValueObjects\LegalEntityUid;

interface LegalEntityRepositoryInterface
{
    public function findByUid(LegalEntityUid $uid): ?LegalEntity;

    public function findByInn(string $inn): ?LegalEntity;

    public function findAll(): array;

    public function findByFilters(array $filters): array;

    public function save(LegalEntity $legalEntity): LegalEntity;

    public function delete(LegalEntityUid $uid): bool;

    public function existsByInn(string $inn): bool;

    public function findByCurator(PersonUid $curatorUid): array;

    public function findByCreator(PersonUid $creatorUid): array;
}
