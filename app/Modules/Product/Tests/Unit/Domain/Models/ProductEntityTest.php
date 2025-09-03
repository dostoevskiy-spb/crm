<?php

declare(strict_types=1);

namespace App\Modules\Product\Tests\Unit\Domain\Models;

use App\Modules\Individual\Domain\ValueObjects\Id;
use App\Modules\Product\Domain\Enum\StatusEnum;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\ValueObjects\Id as DomainProductUid;
use App\Modules\Product\Domain\ValueObjects\Name;
use App\Modules\Product\Domain\ValueObjects\Price;
use App\Modules\Product\Domain\ValueObjects\Type;
use App\Modules\Product\Domain\ValueObjects\Sku;
use App\Modules\Product\Domain\ValueObjects\UnitOfMeasure;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

final class ProductEntityTest extends TestCase
{
    public function test_construct_and_getters(): void
    {
        $creator = new Id((string) Str::uuid());
        $product = new Product(
            name: new Name('Tracker'),
            status: StatusEnum::ACTIVE,
            type: new Type('item'),
            unit: new UnitOfMeasure('pcs'),
            sku: new Sku('TR-001'),
            creatorUid: $creator
        );

        $this->assertSame('Tracker', $product->name()->value());
        $this->assertSame('active', $product->status()->value());
        $this->assertSame('item', $product->type()->value());
        $this->assertSame('pcs', $product->unit()->value());
        $this->assertSame('TR-001', $product->sku()->value());
        $this->assertNotEmpty($product->uid()->value());
        $this->assertInstanceOf(\DateTimeImmutable::class, $product->createdAt());
        $this->assertSame($creator->value(), $product->creatorUid()?->value());
    }

    public function test_set_optional_fields_and_prices(): void
    {
        $product = new Product(
            name: new Name('Tracker'),
            status: StatusEnum::ACTIVE,
            type: new Type('item'),
            unit: new UnitOfMeasure('pcs'),
            sku: new Sku('TR-002')
        );

        $product->setGroupName('Hardware');
        $product->setSubgroupName('GPS');
        $product->setCode1c('A1B2');
        $product->setSalePrice(new Price('100.00'));
        $product->setAvgPurchaseCostYear(new Price('80.50'));
        $product->setLastPurchaseCost(new Price('90.00'));

        $this->assertSame('Hardware', $product->groupName());
        $this->assertSame('GPS', $product->subgroupName());
        $this->assertSame('A1B2', $product->code1c());
        $this->assertSame('100.00', $product->salePrice()?->value());
        $this->assertSame('80.50', $product->avgPurchaseCostYear()?->value());
        $this->assertSame('90.00', $product->lastPurchaseCost()?->value());
    }

    public function test_mutators_and_touch(): void
    {
        $product = new Product(
            name: new Name('Old'),
            status: StatusEnum::INACTIVE,
            type: new Type('service'),
            unit: new UnitOfMeasure('unit'),
            sku: new Sku('TR-003')
        );

        $product->setStatus(StatusEnum::ACTIVE);
        $product->setType(new Type('item'));
        $product->setUnit(new UnitOfMeasure('pcs'));

        $this->assertSame('active', $product->status()->value());
        $this->assertSame('item', $product->type()->value());
        $this->assertSame('pcs', $product->unit()->value());

        $before = $product->updatedAt();
        $updater = new Id((string) Str::uuid());
        $product->touch($updater);

        $this->assertNotNull($product->updatedAt());
        $this->assertSame($updater->value(), $product->updatedByUid()?->value());
    }

    public function test_construct_with_explicit_uid(): void
    {
        $uid = new DomainProductUid((string) Str::uuid());
        $product = new Product(
            name: new Name('Explicit'),
            status: StatusEnum::ACTIVE,
            type: new Type('item'),
            unit: new UnitOfMeasure('pcs'),
            sku: new Sku('TR-004'),
            creatorUid: null,
            uid: $uid
        );
        $this->assertSame($uid->value(), $product->uid()->value());
    }
}
