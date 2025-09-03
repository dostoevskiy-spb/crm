<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Domain\Contracts;

use App\Modules\Equipment\Domain\Models\Equipment;
use App\Modules\Equipment\Domain\ValueObjects\Id;
use Doctrine\Common\Collections\ArrayCollection;

interface EquipmentRepositoryInterface
{
    public function findByUid(Id $uid): ?Equipment;

    /**
     * @return ArrayCollection<Equipment[]>
     */
    public function findAll(): ArrayCollection;

    /**
     * @param  array{uid?:string,name?:string,status?:string,transportUid?:string,warehouse?:string,issuedToUid?:string}  $filters
     * @return ArrayCollection<Equipment[]>
     */
    public function findByFilters(array $filters): ArrayCollection;

    public function save(Equipment $equipment): Equipment;

    public function delete(Id $uid): bool;
}
