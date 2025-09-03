<?php

declare(strict_types=1);

namespace App\Modules\Product\Application\Command;

use App\Modules\Product\Application\DTO\CreateProductDTO;

final readonly class CreateProductCommand
{
    public function __construct(public CreateProductDTO $dto) {}
}
