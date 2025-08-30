<?php

declare(strict_types=1);

namespace App\Application\Individual\Query;

final readonly class GetIndividualQuery
{
    public function __construct(public string $uid) {}
}
