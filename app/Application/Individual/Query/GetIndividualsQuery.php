<?php

declare(strict_types=1);

namespace App\Application\Individual\Query;

final readonly class GetIndividualsQuery
{
    public function __construct(public array $filters = []) {}
}
