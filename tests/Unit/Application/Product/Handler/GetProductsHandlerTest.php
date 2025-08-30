<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Product\Handler;

use App\Application\Product\Handler\GetProductsHandler;
use App\Application\Product\Query\GetProductsQuery;
use App\Domain\Product\Contracts\ProductRepositoryInterface;
use App\Domain\Product\Models\Product;
use App\Domain\Product\ValueObjects\ProductName;
use App\Domain\Product\ValueObjects\ProductStatus;
use App\Domain\Product\ValueObjects\ProductType;
use App\Domain\Product\ValueObjects\Sku;
use App\Domain\Product\ValueObjects\UnitOfMeasure;
use PHPUnit\Framework\TestCase;

final class GetProductsHandlerTest extends TestCase
{
    public function test_maps_products_to_array(): void
    {
        $p1 = new Product(
            name: new ProductName('Alpha'),
            status: ProductStatus::active(),
            type: new ProductType('item'),
            unit: new UnitOfMeasure('шт.'),
            sku: new Sku('A-1')
        );
        $p1->setGroupName('G1');
        $p1->setSubgroupName('SG1');
        $p1->setCode1c('C1');

        $p2 = new Product(
            name: new ProductName('Beta'),
            status: ProductStatus::inactive(),
            type: new ProductType('service'),
            unit: new UnitOfMeasure('усл.'),
            sku: new Sku('B-1')
        );

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('findByFilters')
            ->with(['status' => 'active'])
            ->willReturn([$p1]);

        $handler = new GetProductsHandler($repo);
        $result = $handler(new GetProductsQuery(['status' => 'active']));

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $row = $result[0];
        $this->assertSame('Alpha', $row['name']);
        $this->assertSame('active', $row['status']);
        $this->assertSame('item', $row['type']);
        $this->assertSame('шт.', $row['unit']);
        $this->assertSame('G1', $row['groupName']);
        $this->assertSame('SG1', $row['subgroupName']);
        $this->assertSame('C1', $row['code1c']);
        $this->assertSame('A-1', $row['sku']);
        $this->assertArrayHasKey('createdAt', $row);
        $this->assertArrayHasKey('creatorUid', $row);
    }
}
