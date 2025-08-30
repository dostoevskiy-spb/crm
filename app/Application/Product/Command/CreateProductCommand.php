<?php

declare(strict_types=1);

namespace App\Application\Product\Command;

use App\Application\Product\DTO\CreateProductDTO;

final readonly class CreateProductCommand
{
    public function __construct(public CreateProductDTO $dto) {}
}
