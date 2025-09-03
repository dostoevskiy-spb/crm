<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Application\Query;

final class GetEquipmentsQuery
{
    /** @param array<string,mixed> $filters */
    public function __construct(public array $filters = []) {}
}
