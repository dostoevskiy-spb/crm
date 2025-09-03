<?php

declare(strict_types=1);

namespace App\Modules\Individual\Application\Query;

final readonly class GetIndividualsQuery
{
    public function __construct(public array $filters = []) {}
}
