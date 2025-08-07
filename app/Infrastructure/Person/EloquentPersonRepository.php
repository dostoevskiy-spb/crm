<?php

declare(strict_types=1);

namespace App\Infrastructure\Person;

use App\Domain\Person\Contracts\PersonRepositoryInterface;
use App\Domain\Person\Models\Person as DomainPerson;
use App\Models\Person as EloquentPerson;
use Carbon\Carbon;

class EloquentPersonRepository implements PersonRepositoryInterface
{
    public function __construct(
        private EloquentPerson $eloquentModel
    ) {}

    public function findById(int $id): ?DomainPerson
    {
        $eloquentPerson = $this->eloquentModel->find($id);
        
        return $eloquentPerson ? $this->toDomainModel($eloquentPerson) : null;
    }

    public function findByLogin(string $login): ?DomainPerson
    {
        $eloquentPerson = $this->eloquentModel->where('login', $login)->first();
        
        return $eloquentPerson ? $this->toDomainModel($eloquentPerson) : null;
    }

    public function findAll(): array
    {
        $eloquentPersons = $this->eloquentModel->all();
        
        return $eloquentPersons->map(fn($person) => $this->toDomainModel($person))->toArray();
    }

    public function findByFilters(array $filters): array
    {
        $query = $this->eloquentModel->newQuery();

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['status_id'])) {
            $query->byStatus($filters['status_id']);
        }

        if (isset($filters['creator_id'])) {
            $query->byCreator($filters['creator_id']);
        }

        if (isset($filters['is_company_employee'])) {
            $query->where('is_company_employee', $filters['is_company_employee']);
        }

        if (isset($filters['has_login'])) {
            if ($filters['has_login']) {
                $query->withLogin();
            } else {
                $query->whereNull('login');
            }
        }

        $eloquentPersons = $query->get();
        
        return $eloquentPersons->map(fn($person) => $this->toDomainModel($person))->toArray();
    }

    public function save(DomainPerson $person): DomainPerson
    {
        $eloquentPerson = $person->getId() 
            ? $this->eloquentModel->find($person->getId())
            : new EloquentPerson();

        if (!$eloquentPerson) {
            throw new \RuntimeException("Person with ID {$person->getId()} not found");
        }

        $eloquentPerson->fill([
            'first_name' => $person->getFirstName(),
            'last_name' => $person->getLastName(),
            'middle_name' => $person->getMiddleName(),
            'position_id' => $person->getPositionId(),
            'status_id' => $person->getStatusId(),
            'login' => $person->getLogin(),
            'is_company_employee' => $person->isCompanyEmployee(),
            'creator_id' => $person->getCreatorId(),
        ]);

        if (!$person->getId()) {
            $eloquentPerson->created_at = $person->getCreatedAt();
        }

        $eloquentPerson->save();

        if (!$person->getId()) {
            $person->setId($eloquentPerson->id);
        }

        return $person;
    }

    public function delete(int $id): bool
    {
        $eloquentPerson = $this->eloquentModel->find($id);
        
        return $eloquentPerson ? $eloquentPerson->delete() : false;
    }

    public function existsByLogin(string $login): bool
    {
        return $this->eloquentModel->where('login', $login)->exists();
    }

    public function findCompanyEmployees(): array
    {
        $eloquentPersons = $this->eloquentModel->companyEmployees()->get();
        
        return $eloquentPersons->map(fn($person) => $this->toDomainModel($person))->toArray();
    }

    public function findByCreator(int $creatorId): array
    {
        $eloquentPersons = $this->eloquentModel->byCreator($creatorId)->get();
        
        return $eloquentPersons->map(fn($person) => $this->toDomainModel($person))->toArray();
    }

    public function findByStatus(int $statusId): array
    {
        $eloquentPersons = $this->eloquentModel->byStatus($statusId)->get();
        
        return $eloquentPersons->map(fn($person) => $this->toDomainModel($person))->toArray();
    }

    private function toDomainModel(EloquentPerson $eloquentPerson): DomainPerson
    {
        $domainPerson = new DomainPerson(
            firstName: $eloquentPerson->first_name,
            lastName: $eloquentPerson->last_name,
            middleName: $eloquentPerson->middle_name,
            statusId: $eloquentPerson->status_id,
            creatorId: $eloquentPerson->creator_id,
            positionId: $eloquentPerson->position_id,
            login: $eloquentPerson->login,
            isCompanyEmployee: $eloquentPerson->is_company_employee
        );

        $domainPerson->setId($eloquentPerson->id);
        $domainPerson->setCreatedAt(Carbon::parse($eloquentPerson->created_at));

        return $domainPerson;
    }
}
