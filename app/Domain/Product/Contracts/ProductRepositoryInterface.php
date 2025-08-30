<?php

declare(strict_types=1);

namespace App\Domain\Product\Contracts;

use App\Domain\Individual\ValueObjects\PersonUid;
use App\Domain\Product\Models\Product;
use App\Domain\Product\ValueObjects\ProductUid;
use App\Domain\Product\ValueObjects\Sku;

interface ProductRepositoryInterface
{
    public function findByUid(ProductUid $uid): ?Product;

    public function findBySku(Sku $sku): ?Product;

    public function findByCode1c(string $code1c): ?Product;

    public function findAll(): array;

    public function findByFilters(array $filters): array;

    public function save(Product $product): Product;

    public function delete(ProductUid $uid): bool;

    public function existsBySku(Sku $sku): bool;

    public function existsByCode1c(string $code1c): bool;

    public function findByCreator(PersonUid $creatorUid): array;
}
