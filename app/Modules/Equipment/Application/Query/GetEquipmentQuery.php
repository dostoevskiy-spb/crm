<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Application\Query;

final class GetEquipmentQuery
{
    public function __construct(public string $uid) {}
}
