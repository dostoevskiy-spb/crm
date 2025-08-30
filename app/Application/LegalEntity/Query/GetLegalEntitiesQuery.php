<?php

declare(strict_types=1);

namespace App\Application\LegalEntity\Query;

final readonly class GetLegalEntitiesQuery
{
    public function __construct(public array $filters = []) {}
}
