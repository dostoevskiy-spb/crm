<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\Contracts;

use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\ValueObjects\Id;
use App\Modules\Product\Domain\ValueObjects\Sku;

interface ProductRepositoryInterface
{
    public function findByUid(Id $uid): ?Product;

    public function findBySku(Sku $sku): ?Product;

    public function findByCode1c(string $code1c): ?Product;

    public function findAll(): array;

    public function findByFilters(array $filters): array;

    public function save(Product $product): Product;

    public function delete(Id $uid): bool;

    public function existsBySku(Sku $sku): bool;

    public function existsByCode1c(string $code1c): bool;

    public function findByCreator(\App\Domain\Product\Contracts\Id $creatorUid): array;
}
