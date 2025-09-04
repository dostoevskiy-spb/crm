<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Query;

final readonly class GetUserQuery
{
    public function __construct(public string $uid) {}
}
