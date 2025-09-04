<?php

declare(strict_types=1);

namespace App\Modules\Product\Tests\Integration\Infrastructure;

use App\Modules\Individual\Domain\ValueObjects\Id;
use App\Modules\Product\Domain\Contracts\ProductRepositoryInterface;
use App\Modules\Product\Domain\Enum\StatusEnum;
use App\Modules\Product\Domain\Models\Product as DomainProduct;
use App\Modules\Product\Domain\ValueObjects\Id as DomainProductUid;
use App\Modules\Product\Domain\ValueObjects\Name;
use App\Modules\Product\Domain\ValueObjects\Price;
use App\Modules\Product\Domain\ValueObjects\Sku;
use App\Modules\Product\Domain\ValueObjects\Type;
use App\Modules\Product\Domain\ValueObjects\UnitOfMeasure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class DoctrineProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepositoryInterface $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->app->make(ProductRepositoryInterface::class);
    }

    public function test_save_and_find_by_uid_and_by_sku_and_by_code1c(): void
    {
        $p = new DomainProduct(
            name: new Name('Alpha'),
            status: StatusEnum::ACTIVE,
            type: new Type('item'),
            unit: new UnitOfMeasure('шт.'),
            sku: new Sku('A-1')
        );
        $p->setCode1c('C-1');
        $this->repo->save($p);

        $foundByUid = $this->repo->findByUid();
        $this->assertNotNull($foundByUid);
        $this->assertSame('Alpha', $foundByUid->name()->value());

        $foundBySku = $this->repo->findBySku(new Sku('A-1'));
        $this->assertNotNull($foundBySku);
        $this->assertSame('A-1', $foundBySku->sku()->value());

        $foundByCode = $this->repo->findByCode1c('C-1');
        $this->assertNotNull($foundByCode);
        $this->assertSame('C-1', $foundByCode->code1c());
    }

    public function test_exists_by_sku_and_code1c(): void
    {
        $p = new DomainProduct(
            name: new Name('Beta'),
            status: StatusEnum::INACTIVE,
            type: new Type('service'),
            unit: new UnitOfMeasure('усл.'),
            sku: new Sku('B-1')
        );
        $p->setCode1c('C-2');
        $this->repo->save($p);

        $this->assertTrue($this->repo->existsBySku(new Sku('B-1')));
        $this->assertTrue($this->repo->existsByCode1c('C-2'));
        $this->assertFalse($this->repo->existsBySku(new Sku('NOPE')));
        $this->assertFalse($this->repo->existsByCode1c('NOPE'));
    }

    public function test_find_by_filters_all_supported_fields(): void
    {
        $p1 = new DomainProduct(
            name: new Name('KVS Tracker'),
            status: StatusEnum::ACTIVE,
            type: new Type('item'),
            unit: new UnitOfMeasure('шт.'),
            sku: new Sku('KV-1')
        );
        $p1->setGroupName('Оборудование');
        $p1->setSubgroupName('Трекеры');
        $p1->setCode1c('KVS-1C');
        $p1->setSalePrice(new Price('100.00'));

        $p2 = new DomainProduct(
            name: new Name('Other Service'),
            status: StatusEnum::INACTIVE,
            type: new Type('service'),
            unit: new UnitOfMeasure('усл.'),
            sku: new Sku('OS-1')
        );
        $p2->setGroupName('Услуги');
        $p2->setSubgroupName('Сервис');
        $p2->setCode1c('OS-1C');

        $this->repo->save($p1);
        $this->repo->save($p2);

        $this->assertCount(1, $this->repo->findByFilters(['name' => 'Tracker']));
        $this->assertCount(1, $this->repo->findByFilters(['status' => 'inactive']));
        $this->assertCount(1, $this->repo->findByFilters(['type' => 'service']));
        $this->assertCount(1, $this->repo->findByFilters(['unit' => 'шт.']));
        $this->assertCount(1, $this->repo->findByFilters(['group' => 'Оборудование']));
        $this->assertCount(1, $this->repo->findByFilters(['subgroup' => 'Сервис']));
        $this->assertCount(1, $this->repo->findByFilters(['code1c' => 'OS-1C']));
        $this->assertCount(1, $this->repo->findByFilters(['sku' => 'KV-1']));
    }

    public function test_delete_and_find_by_creator(): void
    {
        $creator1 = new Id((string) Str::uuid());
        $creator2 = new Id((string) Str::uuid());

        $p1 = new DomainProduct(
            name: new Name('DelMe'),
            status: StatusEnum::ACTIVE,
            type: new Type('item'),
            unit: new UnitOfMeasure('шт.'),
            sku: new Sku('DEL-1'),
            creatorUid: $creator1
        );
        $p2 = new DomainProduct(
            name: new Name('KeepMe'),
            status: StatusEnum::ACTIVE,
            type: new Type('item'),
            unit: new UnitOfMeasure('шт.'),
            sku: new Sku('KEP-1'),
            creatorUid: $creator2
        );
        $this->repo->save($p1);
        $this->repo->save($p2);

        $foundByCreator1 = $this->repo->findByCreator($creator1);
        $this->assertCount(1, $foundByCreator1);
        $this->assertSame('DelMe', $foundByCreator1[0]->name()->value());

        $deleted = $this->repo->delete(new DomainProductUid($p1->uid()->value()));
        $this->assertTrue($deleted);
        $this->assertNull($this->repo->findByUid(new DomainProductUid($p1->uid()->value())));

        $foundByCreator1After = $this->repo->findByCreator($creator1);
        $this->assertCount(0, $foundByCreator1After);
    }
}
