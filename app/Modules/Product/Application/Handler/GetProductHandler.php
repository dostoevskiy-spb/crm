<?php

declare(strict_types=1);

namespace App\Modules\Product\Application\Handler;

use App\Modules\Product\Application\Query\GetProductQuery;
use App\Modules\Product\Domain\Contracts\ProductRepositoryInterface;
use App\Modules\Product\Domain\ValueObjects\Id;

final class GetProductHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function __invoke(GetProductQuery $query): ?array
    {
        $uid = new Id($query->uid);
        $product = $this->productRepository->findByUid($uid);

        if (! $product) {
            return null;
        }

        return [
            'uid' => $product->uid()->value(),
            'name' => $product->name()->value(),
            'status' => $product->status()->value(),
            'type' => $product->type()->value(),
            'unit' => $product->unit()->value(),
            'groupName' => $product->groupName(),
            'subgroupName' => $product->subgroupName(),
            'code1c' => $product->code1c(),
            'sku' => $product->sku()->value(),
            'salePrice' => $product->salePrice()?->value(),
            'avgPurchaseCostYear' => $product->avgPurchaseCostYear()?->value(),
            'lastPurchaseCost' => $product->lastPurchaseCost()?->value(),
            'createdAt' => $product->createdAt()->format('Y-m-d H:i:s'),
            'creatorUid' => $product->creatorUid()?->value(),
            'updatedAt' => $product->updatedAt()?->format('Y-m-d H:i:s'),
            'updatedByUid' => $product->updatedByUid()?->value(),
        ];
    }
}
