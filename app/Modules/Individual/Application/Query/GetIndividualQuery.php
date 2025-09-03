<?php

declare(strict_types=1);

namespace App\Modules\Individual\Application\Query;

final readonly class GetIndividualQuery
{
    public function __construct(public string $uid) {}
}
