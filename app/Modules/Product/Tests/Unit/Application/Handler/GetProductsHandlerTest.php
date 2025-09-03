<?php

declare(strict_types=1);

namespace App\Modules\Product\Tests\Unit\Application\Handler;

use App\Modules\Product\Application\Handler\GetProductsHandler;
use App\Modules\Product\Application\Query\GetProductsQuery;
use App\Modules\Product\Domain\Contracts\ProductRepositoryInterface;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\ValueObjects\ProductName;
use App\Modules\Product\Domain\ValueObjects\ProductStatus;
use App\Modules\Product\Domain\ValueObjects\ProductType;
use App\Modules\Product\Domain\ValueObjects\Sku;
use App\Modules\Product\Domain\ValueObjects\UnitOfMeasure;
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
