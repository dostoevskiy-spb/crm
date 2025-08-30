<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Individual\ValueObjects\PersonUid;
use App\Domain\LegalEntity\Contracts\LegalEntityRepositoryInterface;
use App\Domain\LegalEntity\Models\LegalEntity as DomainLegalEntity;
use App\Domain\LegalEntity\ValueObjects\CompanyName;
use App\Domain\LegalEntity\ValueObjects\TaxNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LegalEntityControllerTest extends TestCase
{
    use RefreshDatabase;

    private LegalEntityRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(LegalEntityRepositoryInterface::class);
    }

    public function test_create_legal_entity_success(): void
    {
        $data = [
            'shortName' => 'KVS',
            'fullName' => 'KVS Systems LLC',
            'ogrn' => '1234567890123',
            'inn' => '1234567890',
            'kpp' => '123456789',
            'legalAddress' => 'Saint-Petersburg',
            'phoneNumber' => '+78120000000',
            'email' => 'office@example.com',
        ];

        $response = $this->postJson('/api/legal-entities', $data);

        $response->assertStatus(201)
                 ->assertJsonStructure(['uid']);

        $uid = $response->json('uid');
        $this->assertIsString($uid);
        $this->assertMatchesRegularExpression('/^[0-9a-fA-F-]{36}$/', $uid);

        // verify via read API that entity persisted correctly
        $show = $this->getJson("/api/legal-entities/{$uid}");
        $show->assertStatus(200)
             ->assertJson([
                 'uid' => $uid,
                 'shortName' => 'KVS',
                 'fullName' => 'KVS Systems LLC',
                 'ogrn' => '1234567890123',
                 'inn' => '1234567890',
                 'kpp' => '123456789',
                 'legalAddress' => 'Saint-Petersburg',
                 'phoneNumber' => '+78120000000',
                 'email' => 'office@example.com',
             ]);
    }

    public function test_create_legal_entity_duplicate_inn(): void
    {
        $entity = new DomainLegalEntity(
            name: new CompanyName('KVS', 'KVS Systems LLC'),
            taxNumber: new TaxNumber('1234567890123', '1234567890', '123456789'),
            creatorUid: null
        );
        $this->repository->save($entity);

        $data = [
            'shortName' => 'Another',
            'fullName' => 'Another LLC',
            'ogrn' => '1234567890123',
            'inn' => '1234567890',
            'kpp' => '123456789',
        ];

        $response = $this->postJson('/api/legal-entities', $data);

        $response->assertStatus(400)
                 ->assertJson(['error' => 'Legal entity with this INN already exists']);
    }

    public function test_create_legal_entity_validation_error_inn(): void
    {
        $data = [
            'shortName' => 'KVS',
            'fullName' => 'KVS Systems LLC',
            'ogrn' => '1234567890123',
            'inn' => '123',
            'kpp' => '123456789',
        ];

        $response = $this->postJson('/api/legal-entities', $data);

        $response->assertStatus(400)
                 ->assertJsonFragment(['error' => 'INN must contain exactly 10 digits']);
    }

    public function test_show_legal_entity_found(): void
    {
        $creatorUid = new PersonUid((string) Str::uuid());
        $entity = new DomainLegalEntity(
            name: new CompanyName('KVS', 'KVS Systems LLC'),
            taxNumber: new TaxNumber('1234567890123', '1234567890', '123456789'),
            creatorUid: $creatorUid
        );
        $entity->setLegalAddress('Saint-Petersburg');
        $entity->setPhoneNumber('+78120000000');
        $entity->setEmail('office@example.com');
        $this->repository->save($entity);

        $uid = $entity->uid()->value();
        $response = $this->getJson("/api/legal-entities/{$uid}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'uid','shortName','fullName','ogrn','inn','kpp',
                    'legalAddress','phoneNumber','email','createdAt','creatorUid','curatorUid'
                 ])
                 ->assertJson([
                    'uid' => $uid,
                    'shortName' => 'KVS',
                    'fullName' => 'KVS Systems LLC',
                    'ogrn' => '1234567890123',
                    'inn' => '1234567890',
                    'kpp' => '123456789',
                    'legalAddress' => 'Saint-Petersburg',
                    'phoneNumber' => '+78120000000',
                    'email' => 'office@example.com',
                    'creatorUid' => $creatorUid->value(),
                 ]);
    }

    public function test_show_legal_entity_not_found(): void
    {
        $response = $this->getJson('/api/legal-entities/' . (string) Str::uuid());
        $response->assertStatus(404)
                 ->assertJson(['error' => 'Legal entity not found']);
    }

    public function test_index_list_all(): void
    {
        $e1 = new DomainLegalEntity(
            name: new CompanyName('Alpha', 'Alpha LLC'),
            taxNumber: new TaxNumber('1234567890123', '1111111111', '123456789'),
            creatorUid: null
        );
        $e2 = new DomainLegalEntity(
            name: new CompanyName('Beta', 'Beta LLC'),
            taxNumber: new TaxNumber('1234567890123', '2222222222', '123456789'),
            creatorUid: null
        );
        $this->repository->save($e1);
        $this->repository->save($e2);

        $response = $this->getJson('/api/legal-entities');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertCount(2, $data);
    }

    public function test_index_with_filters(): void
    {
        $curator = new PersonUid((string) Str::uuid());

        $e1 = new DomainLegalEntity(
            name: new CompanyName('KVS', 'KVS Systems LLC'),
            taxNumber: new TaxNumber('1234567890123', '3333333333', '123456789'),
            creatorUid: null
        );
        $e1->setPhoneNumber('+78120000000');
        $e1->setEmail('office@kvs.local');
        $e1->setCuratorUid($curator);

        $e2 = new DomainLegalEntity(
            name: new CompanyName('Other', 'Other LLC'),
            taxNumber: new TaxNumber('1234567890123', '4444444444', '123456789'),
            creatorUid: null
        );
        $e2->setPhoneNumber('+74950000000');
        $e2->setEmail('info@other.local');

        $this->repository->save($e1);
        $this->repository->save($e2);

        // shortName LIKE
        $resp1 = $this->getJson('/api/legal-entities?shortName=KV');
        $resp1->assertStatus(200);
        $this->assertCount(1, $resp1->json());

        // inn exact
        $resp2 = $this->getJson('/api/legal-entities?inn=4444444444');
        $resp2->assertStatus(200);
        $this->assertCount(1, $resp2->json());

        // phone LIKE
        $resp3 = $this->getJson('/api/legal-entities?phoneNumber=+7812');
        $resp3->assertStatus(200);
        $this->assertCount(1, $resp3->json());

        // email LIKE
        $resp4 = $this->getJson('/api/legal-entities?email=@other.');
        $resp4->assertStatus(200);
        $this->assertCount(1, $resp4->json());

        // curatorUid
        $resp5 = $this->getJson('/api/legal-entities?curatorUid=' . $curator->value());
        $resp5->assertStatus(200);
        $this->assertCount(1, $resp5->json());
    }
}
