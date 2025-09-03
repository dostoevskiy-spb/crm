<?php

declare(strict_types=1);

namespace App\Modules\Product\Tests\Feature;

use App\Modules\Individual\Domain\ValueObjects\Id;
use App\Modules\Product\Domain\Contracts\ProductRepositoryInterface;
use App\Modules\Product\Domain\Enum\StatusEnum;
use App\Modules\Product\Domain\Models\Product as DomainProduct;
use App\Modules\Product\Domain\ValueObjects\Name;
use App\Modules\Product\Domain\ValueObjects\Price;
use App\Modules\Product\Domain\ValueObjects\Sku;
use App\Modules\Product\Domain\ValueObjects\Type;
use App\Modules\Product\Domain\ValueObjects\UnitOfMeasure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(ProductRepositoryInterface::class);
    }

    public function test_create_product_success(): void
    {
        $data = [
            'name' => 'GPS-трекер',
            'status' => 'active',
            'type' => 'item',
            'unit' => 'шт.',
            'sku' => 'GPS-123',
            'groupName' => 'Оборудование',
            'subgroupName' => 'Трекеры',
            'code1c' => 'A1B2C3',
            'salePrice' => '5990.00',
        ];

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['uid']);

        $uid = $response->json('uid');
        $this->assertIsString($uid);
        $this->assertMatchesRegularExpression('/^[0-9a-fA-F-]{36}$/', $uid);

        $show = $this->getJson("/api/products/{$uid}");
        $show->assertStatus(200)
            ->assertJson([
                'uid' => $uid,
                'name' => 'GPS-трекер',
                'status' => 'active',
                'type' => 'item',
                'unit' => 'шт.',
                'groupName' => 'Оборудование',
                'subgroupName' => 'Трекеры',
                'code1c' => 'A1B2C3',
                'sku' => 'GPS-123',
                'salePrice' => '5990.00',
            ]);
    }

    public function test_create_product_duplicate_sku(): void
    {
        $p = new DomainProduct(
            name: new Name('Base Product'),
            status: StatusEnum::ACTIVE,
            type: new Type('item'),
            unit: new UnitOfMeasure('шт.'),
            sku: new Sku('DUP-001'),
            creatorUid: null
        );
        $this->repository->save($p);

        $data = [
            'name' => 'Another',
            'status' => 'active',
            'type' => 'item',
            'unit' => 'шт.',
            'sku' => 'DUP-001',
        ];

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Product with this SKU already exists']);
    }

    public function test_create_product_validation_error_name(): void
    {
        $data = [
            'name' => '',
            'status' => 'active',
            'type' => 'item',
            'unit' => 'шт.',
            'sku' => 'VAL-001',
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(400)
            ->assertJsonFragment(['error' => 'Product name must be between 1 and 50 characters']);
    }

    public function test_show_product_found(): void
    {
        $creatorUid = new Id((string) Str::uuid());
        $p = new DomainProduct(
            name: new Name('X-Tracker'),
            status: StatusEnum::ACTIVE,
            type: new Type('item'),
            unit: new UnitOfMeasure('шт.'),
            sku: new Sku('XTR-001'),
            creatorUid: $creatorUid
        );
        $p->setGroupName('Оборудование');
        $p->setSubgroupName('Трекеры');
        $p->setCode1c('XT-1C');
        $p->setSalePrice(new Price('4500.00'));
        $this->repository->save($p);

        $uid = $p->uid()->value();
        $response = $this->getJson("/api/products/{$uid}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'uid', 'name', 'status', 'type', 'unit', 'sku', 'createdAt', 'creatorUid',
            ])
            ->assertJson([
                'uid' => $uid,
                'name' => 'X-Tracker',
                'status' => 'active',
                'type' => 'item',
                'unit' => 'шт.',
                'sku' => 'XTR-001',
                'creatorUid' => $creatorUid->value(),
            ]);
    }

    public function test_show_product_not_found(): void
    {
        $response = $this->getJson('/api/products/'.(string) Str::uuid());
        $response->assertStatus(404)
            ->assertJson(['error' => 'Product not found']);
    }

    public function test_index_list_all(): void
    {
        $p1 = new DomainProduct(
            name: new Name('Alpha'),
            status: StatusEnum::ACTIVE,
            type: new Type('item'),
            unit: new UnitOfMeasure('шт.'),
            sku: new Sku('A-1')
        );
        $p2 = new DomainProduct(
            name: new Name('Beta'),
            status: StatusEnum::INACTIVE,
            type: new Type('service'),
            unit: new UnitOfMeasure('усл.'),
            sku: new Sku('B-1')
        );
        $this->repository->save($p1);
        $this->repository->save($p2);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertCount(2, $data);
    }

    public function test_index_with_filters(): void
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

        $this->repository->save($p1);
        $this->repository->save($p2);

        // name LIKE
        $resp1 = $this->getJson('/api/products?name=Tracker');
        $resp1->assertStatus(200);
        $this->assertCount(1, $resp1->json());

        // status exact
        $resp2 = $this->getJson('/api/products?status=inactive');
        $resp2->assertStatus(200);
        $this->assertCount(1, $resp2->json());

        // type exact
        $resp3 = $this->getJson('/api/products?type=service');
        $resp3->assertStatus(200);
        $this->assertCount(1, $resp3->json());

        // unit exact via embeddable
        $resp4 = $this->getJson('/api/products?unit=шт.');
        $resp4->assertStatus(200);
        $this->assertCount(1, $resp4->json());

        // group & subgroup
        $resp5 = $this->getJson('/api/products?group=Оборудование');
        $resp5->assertStatus(200);
        $this->assertCount(1, $resp5->json());

        $resp6 = $this->getJson('/api/products?subgroup=Сервис');
        $resp6->assertStatus(200);
        $this->assertCount(1, $resp6->json());

        // code1c exact
        $resp7 = $this->getJson('/api/products?code1c=OS-1C');
        $resp7->assertStatus(200);
        $this->assertCount(1, $resp7->json());

        // sku exact
        $resp8 = $this->getJson('/api/products?sku=KV-1');
        $resp8->assertStatus(200);
        $this->assertCount(1, $resp8->json());
    }
}
