<?php

declare(strict_types=1);

namespace App\Domain\Person\Contracts;

use App\Domain\Person\Models\Person;

interface PersonRepositoryInterface
{
    public function findById(int $id): ?Person;
    
    public function findByLogin(string $login): ?Person;
    
    public function findAll(): array;
    
    public function findByFilters(array $filters): array;
    
    public function save(Person $person): Person;
    
    public function delete(int $id): bool;
    
    public function existsByLogin(string $login): bool;
    
    public function findCompanyEmployees(): array;
    
    public function findByCreator(int $creatorId): array;
    
    public function findByStatus(int $statusId): array;
}
