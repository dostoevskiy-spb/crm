<?php

declare(strict_types=1);

namespace App\Application\Equipment\Query;

final class GetEquipmentQuery
{
    public function __construct(public string $uid) {}
}
