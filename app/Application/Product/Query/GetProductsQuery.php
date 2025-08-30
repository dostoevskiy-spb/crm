<?php

declare(strict_types=1);

namespace App\Application\Product\Query;

final readonly class GetProductsQuery
{
    public function __construct(public array $filters = []) {}
}
