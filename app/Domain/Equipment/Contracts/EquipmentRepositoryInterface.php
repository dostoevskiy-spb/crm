<?php

declare(strict_types=1);

namespace App\Domain\Equipment\Contracts;

use App\Domain\Equipment\Models\Equipment;
use App\Domain\Equipment\ValueObjects\EquipmentUid;

interface EquipmentRepositoryInterface
{
    public function findByUid(EquipmentUid $uid): ?Equipment;

    /**
     * @return Equipment[]
     */
    public function findAll(): array;

    /**
     * @param array{uid?:string,name?:string,status?:string,transportUid?:string,warehouse?:string,issuedToUid?:string} $filters
     * @return Equipment[]
     */
    public function findByFilters(array $filters): array;

    public function save(Equipment $equipment): Equipment;

    public function delete(EquipmentUid $uid): bool;
}
