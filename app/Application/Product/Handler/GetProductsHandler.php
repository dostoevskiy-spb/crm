<?php

declare(strict_types=1);

namespace App\Application\Product\Handler;

use App\Application\Product\Query\GetProductsQuery;
use App\Domain\Product\Contracts\ProductRepositoryInterface;

final readonly class GetProductsHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function __invoke(GetProductsQuery $query): array
    {
        $products = $this->productRepository->findByFilters($query->filters);

        return array_map(function ($product) {
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
                'createdAt' => $product->createdAt()->format('Y-m-d H:i:s'),
                'creatorUid' => $product->creatorUid()?->value(),
            ];
        }, $products);
    }
}
