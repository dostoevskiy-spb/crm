<?php

declare(strict_types=1);

namespace App\Application\Product\DTO;

final readonly class CreateProductDTO
{
    public function __construct(
        public string $name,
        public string $status, // active|inactive
        public string $type,   // item|service
        public string $unit,
        public string $sku,
        public ?string $groupName = null,
        public ?string $subgroupName = null,
        public ?string $code1c = null,
        public ?string $salePrice = null,
        public ?string $creatorUid = null
    ) {}
}
