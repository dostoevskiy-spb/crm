<?php

declare(strict_types=1);

namespace App\Modules\Product\Application\Handler;

use App\Modules\Individual\Domain\ValueObjects\Id;
use App\Modules\Product\Application\Command\CreateProductCommand;
use App\Modules\Product\Domain\Contracts\ProductRepositoryInterface;
use App\Modules\Product\Domain\Enum\StatusEnum;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\ValueObjects\Name;
use App\Modules\Product\Domain\ValueObjects\Price;
use App\Modules\Product\Domain\ValueObjects\Type;
use App\Modules\Product\Domain\ValueObjects\Sku;
use App\Modules\Product\Domain\ValueObjects\UnitOfMeasure;

final readonly class CreateProductHandler
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

        $name = new Name($dto->name);
        $status = StatusEnum::tryFrom($dto->status);
        $type = new Type($dto->type);
        $unit = new UnitOfMeasure($dto->unit);
        $creatorUid = $dto->creatorUid ? new Id($dto->creatorUid) : null;

        $product = new Product($name, $status, $type, $unit, $sku, $creatorUid);

        $product->setGroupName($dto->groupName);
        $product->setSubgroupName($dto->subgroupName);
        $product->setCode1c($dto->code1c);

        if ($dto->salePrice !== null && $dto->salePrice !== '') {
            $product->setSalePrice(new Price($dto->salePrice));
        }

        $saved = $this->productRepository->save($product);

        return $saved->uid()->value();
    }
}
