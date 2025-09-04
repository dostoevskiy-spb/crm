<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Query;

final readonly class GetUsersQuery
{
    /** @param array<string,mixed> $filters */
    public function __construct(public array $filters = []) {}
}
