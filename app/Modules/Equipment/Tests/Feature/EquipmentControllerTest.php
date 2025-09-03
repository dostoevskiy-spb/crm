<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Tests\Feature;

use App\Modules\Equipment\Domain\Contracts\EquipmentRepositoryInterface;
use App\Modules\Equipment\Domain\Models\Equipment as DomainEquipment;
use App\Modules\Equipment\Domain\ValueObjects\Name;
use App\Modules\Individual\Domain\ValueObjects\Id;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EquipmentControllerTest extends TestCase
{
    use RefreshDatabase;

    private EquipmentRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(EquipmentRepositoryInterface::class);
    }

    public function test_show_equipment_found(): void
    {
        $creatorUid = new Id((string) Str::uuid());
        $e = new DomainEquipment(
            name: new Name('KVS Tracker'),
            status: new EquipmentStatus('warehouse'),
            creatorUid: $creatorUid
        );
        $e->setTransportUid((string) Str::uuid());
        $e->setWarehouse('Main');
        $e->setIssuedToUid(new Id((string) Str::uuid()));
        $this->repository->save($e);

        $uid = $e->uid()->value();
        $response = $this->getJson("/api/equipment/{$uid}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'uid', 'name', 'status', 'createdAt',
            ])
            ->assertJson([
                'uid' => $uid,
                'name' => 'KVS Tracker',
                'status' => 'warehouse',
                'creatorUid' => $creatorUid->value(),
            ]);
    }

    public function test_show_equipment_not_found(): void
    {
        $response = $this->getJson('/api/equipment/'.(string) Str::uuid());
        $response->assertStatus(404)
            ->assertJson(['error' => 'Equipment not found']);
    }

    public function test_show_equipment_invalid_uid(): void
    {
        $response = $this->getJson('/api/equipment/not-a-uuid');
        // Из-за ограничения маршрута по regex, некорректный UID не совпадает с маршрутом
        // и фреймворк возвращает 404 Not Found.
        $response->assertStatus(404);
    }

    public function test_index_list_all(): void
    {
        $e1 = new DomainEquipment(
            name: new Name('Alpha Device'),
            status: new EquipmentStatus('warehouse')
        );
        $e2 = new DomainEquipment(
            name: new Name('Beta Device'),
            status: new EquipmentStatus('issued')
        );
        $this->repository->save($e1);
        $this->repository->save($e2);

        $response = $this->getJson('/api/equipment');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertCount(2, $data);
    }

    public function test_index_with_filters(): void
    {
        $e1 = new DomainEquipment(
            name: new Name('KVS Tracker A'),
            status: new EquipmentStatus('warehouse')
        );

        $e2 = new DomainEquipment(
            name: new Name('Service Device'),
            status: new EquipmentStatus('issued')
        );
        $transportUid = (string) Str::uuid();
        $issuedTo = new Id((string) Str::uuid());
        $e2->setTransportUid($transportUid);
        $e2->setWarehouse('SPB');
        $e2->setIssuedToUid($issuedTo);

        $this->repository->save($e1);
        $this->repository->save($e2);

        // name LIKE
        $resp1 = $this->getJson('/api/equipment?name=Tracker');
        $resp1->assertStatus(200);
        $this->assertCount(1, $resp1->json());

        // status exact
        $resp2 = $this->getJson('/api/equipment?status=issued');
        $resp2->assertStatus(200);
        $this->assertCount(1, $resp2->json());

        // transportUid exact
        $resp3 = $this->getJson('/api/equipment?transportUid='.$transportUid);
        $resp3->assertStatus(200);
        $this->assertCount(1, $resp3->json());

        // warehouse exact
        $resp4 = $this->getJson('/api/equipment?warehouse=SPB');
        $resp4->assertStatus(200);
        $this->assertCount(1, $resp4->json());

        // issuedToUid exact
        $resp5 = $this->getJson('/api/equipment?issuedToUid='.$issuedTo->value());
        $resp5->assertStatus(200);
        $this->assertCount(1, $resp5->json());

        // uid exact
        $resp6 = $this->getJson('/api/equipment?uid='.$e1->uid()->value());
        $resp6->assertStatus(200);
        $this->assertCount(1, $resp6->json());
    }
}
