<?php

declare(strict_types=1);

namespace App\Modules\Product\Tests\Unit\Application\Handler;

use App\Modules\Individual\Domain\ValueObjects\Id;
use App\Modules\Product\Application\Handler\GetProductHandler;
use App\Modules\Product\Application\Query\GetProductQuery;
use App\Modules\Product\Domain\Contracts\ProductRepositoryInterface;
use App\Modules\Product\Domain\Enum\StatusEnum;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\ValueObjects\Name;
use App\Modules\Product\Domain\ValueObjects\Price;
use App\Modules\Product\Domain\ValueObjects\Sku;
use App\Modules\Product\Domain\ValueObjects\Type;
use App\Modules\Product\Domain\ValueObjects\UnitOfMeasure;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

final class GetProductHandlerTest extends TestCase
{
    public function test_returns_array_when_product_found(): void
    {
        $creator = new Id((string) Str::uuid());
        $updater = new Id((string) Str::uuid());

        $product = new Product(
            name: new Name('X-Tracker'),
            status: StatusEnum::ACTIVE,
            type: new Type('item'),
            unit: new UnitOfMeasure('шт.'),
            sku: new Sku('XTR-001'),
            creatorUid: $creator
        );
        $product->setGroupName('Оборудование');
        $product->setSubgroupName('Трекеры');
        $product->setCode1c('XT-1C');
        $product->setSalePrice(new Price('4500.00'));
        $product->setAvgPurchaseCostYear(new Price('3000.00'));
        $product->setLastPurchaseCost(new Price('3200.00'));
        $product->touch($updater);

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('findByUid')
            ->willReturn($product);

        $handler = new GetProductHandler($repo);
        $result = $handler(new GetProductQuery($product->uid()->value()));

        $this->assertIsArray($result);
        $this->assertSame($product->uid()->value(), $result['uid']);
        $this->assertSame('X-Tracker', $result['name']);
        $this->assertSame('active', $result['status']);
        $this->assertSame('item', $result['type']);
        $this->assertSame('шт.', $result['unit']);
        $this->assertSame('Оборудование', $result['groupName']);
        $this->assertSame('Трекеры', $result['subgroupName']);
        $this->assertSame('XT-1C', $result['code1c']);
        $this->assertSame('XTR-001', $result['sku']);
        $this->assertSame('4500.00', $result['salePrice']);
        $this->assertSame('3000.00', $result['avgPurchaseCostYear']);
        $this->assertSame('3200.00', $result['lastPurchaseCost']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $result['createdAt']);
        $this->assertSame($creator->value(), $result['creatorUid']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $result['updatedAt']);
        $this->assertSame($updater->value(), $result['updatedByUid']);
    }

    public function test_returns_null_when_not_found(): void
    {
        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('findByUid')
            ->willReturn(null);

        $handler = new GetProductHandler($repo);
        $result = $handler(new GetProductQuery((string) Str::uuid()));

        $this->assertNull($result);
    }
}
