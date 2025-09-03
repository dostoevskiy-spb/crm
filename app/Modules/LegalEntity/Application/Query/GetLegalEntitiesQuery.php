<?php

declare(strict_types=1);

namespace App\Modules\LegalEntity\Application\Query;

final readonly class GetLegalEntitiesQuery
{
    public function __construct(public array $filters = []) {}
}
