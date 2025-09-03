<?php

declare(strict_types=1);

namespace App\Modules\Product\Application\Query;

final readonly class GetProductQuery
{
    public function __construct(public string $uid) {}
}
