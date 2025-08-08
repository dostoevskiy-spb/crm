<?php

declare(strict_types=1);

namespace App\Infrastructure\Person;

use App\Domain\Individual\Contracts\IndividualRepositoryInterface;
use App\Domain\Individual\Models\Individual as DomainIndividual;
use App\Models\Individual as EloquentIndividual;
use Carbon\Carbon;

class EloquentIndividualRepository implements IndividualRepositoryInterface
{
    private string $modelClass = EloquentIndividual::class;

    public function findById(int $id): ?DomainIndividual
    {
        $eloquentPerson = $this->modelClass::find($id);

        return $eloquentPerson ? $this->toDomainModel($eloquentPerson) : null;
    }

    public function findByLogin(string $login): ?DomainIndividual
    {
        $eloquentPerson = $this->modelClass::where('login', $login)->first();

        return $eloquentPerson ? $this->toDomainModel($eloquentPerson) : null;
    }

    public function findAll(): array
    {
        $eloquentPersons = $this->modelClass::all();

        return $eloquentPersons->map(fn($person) => $this->toDomainModel($person))->toArray();
    }

    public function findByFilters(array $filters): array
    {
        $query = $this->modelClass::query();

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        if (isset($filters['creator_id'])) {
            $query->where('creator_id', $filters['creator_id']);
        }

        if (isset($filters['is_company_employee'])) {
            $query->where('is_company_employee', $filters['is_company_employee']);
        }

        if (isset($filters['has_login'])) {
            if ($filters['has_login']) {
                $query->whereNotNull('login');
            } else {
                $query->whereNull('login');
            }
        }

        $eloquentPersons = $query->get();

        return $eloquentPersons->map(fn($person) => $this->toDomainModel($person))->toArray();
    }

    public function save(DomainIndividual $person): DomainIndividual
    {
        $eloquentPerson = $person->getId()
            ? $this->modelClass::find($person->getId())
            : new $this->modelClass();

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
        $eloquentPerson = $this->modelClass::find($id);

        return $eloquentPerson ? $eloquentPerson->delete() : false;
    }

    public function existsByLogin(string $login): bool
    {
        return $this->modelClass::where('login', $login)->exists();
    }

    public function findCompanyEmployees(): array
    {
        $eloquentPersons = $this->modelClass::companyEmployees()->get();

        return $eloquentPersons->map(fn($person) => $this->toDomainModel($person))->toArray();
    }

    public function findByCreator(int $creatorId): array
    {
        $eloquentPersons = $this->modelClass::byCreator($creatorId)->get();

        return $eloquentPersons->map(fn($person) => $this->toDomainModel($person))->toArray();
    }

    public function findByStatus(int $statusId): array
    {
        $eloquentPersons = $this->modelClass::byStatus($statusId)->get();

        return $eloquentPersons->map(fn($person) => $this->toDomainModel($person))->toArray();
    }

    private function toDomainModel(EloquentIndividual $eloquentIndividual): DomainIndividual
    {
        $domainPerson = new DomainIndividual(
            firstName: $eloquentIndividual->first_name,
            lastName: $eloquentIndividual->last_name,
            middleName: $eloquentIndividual->middle_name,
            statusId: $eloquentIndividual->status_id,
            creatorId: $eloquentIndividual->creator_id,
            positionId: $eloquentIndividual->position_id,
            login: $eloquentIndividual->login,
            isCompanyEmployee: $eloquentIndividual->is_company_employee
        );

        $domainPerson->setId($eloquentIndividual->id);
        $domainPerson->setCreatedAt(Carbon::parse($eloquentIndividual->created_at));

        return $domainPerson;
    }
}
