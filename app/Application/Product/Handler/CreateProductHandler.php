<?php

declare(strict_types=1);

namespace App\Application\Product\Handler;

use App\Application\Product\Command\CreateProductCommand;
use App\Domain\Individual\ValueObjects\PersonUid;
use App\Domain\Product\Contracts\ProductRepositoryInterface;
use App\Domain\Product\Models\Product;
use App\Domain\Product\ValueObjects\ProductName;
use App\Domain\Product\ValueObjects\ProductPrice;
use App\Domain\Product\ValueObjects\ProductStatus;
use App\Domain\Product\ValueObjects\ProductType;
use App\Domain\Product\ValueObjects\Sku;
use App\Domain\Product\ValueObjects\UnitOfMeasure;

final class CreateProductHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function __invoke(CreateProductCommand $command): string
    {
        $dto = $command->dto;

        $sku = new Sku($dto->sku);
        if ($this->productRepository->existsBySku($sku)) {
            throw new \InvalidArgumentException('Product with this SKU already exists');
        }
        if ($dto->code1c !== null && $dto->code1c !== '' && $this->productRepository->existsByCode1c($dto->code1c)) {
            throw new \InvalidArgumentException('Product with this 1C code already exists');
        }

        $name = new ProductName($dto->name);
        $status = new ProductStatus($dto->status);
        $type = new ProductType($dto->type);
        $unit = new UnitOfMeasure($dto->unit);
        $creatorUid = $dto->creatorUid ? new PersonUid($dto->creatorUid) : null;

        $product = new Product($name, $status, $type, $unit, $sku, $creatorUid);

        $product->setGroupName($dto->groupName);
        $product->setSubgroupName($dto->subgroupName);
        $product->setCode1c($dto->code1c);

        if ($dto->salePrice !== null && $dto->salePrice !== '') {
            $product->setSalePrice(new ProductPrice($dto->salePrice));
        }

        $saved = $this->productRepository->save($product);
        return $saved->uid()->value();
    }
}
