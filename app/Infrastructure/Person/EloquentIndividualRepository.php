<?php

declare(strict_types=1);

namespace App\Infrastructure\Person;

use App\Domain\Individual\Contracts\IndividualRepositoryInterface;
use App\Domain\Individual\Models\Individual as DomainIndividual;
use App\Domain\Individual\ValueObjects\Login;
use App\Domain\Individual\ValueObjects\Name;
use App\Domain\Individual\ValueObjects\PersonStatus;
use App\Domain\Individual\ValueObjects\PersonUid;
use App\Models\Individual as EloquentIndividual;
use Carbon\Carbon;

class EloquentIndividualRepository implements IndividualRepositoryInterface
{
    private string $modelClass = EloquentIndividual::class;

    public function findByUid(PersonUid $uid): ?DomainIndividual
    {
        $eloquentPerson = $this->modelClass::query()->where('uid', $uid->value())->first();

        return $eloquentPerson ? $this->toDomainModel($eloquentPerson) : null;
    }

    public function findByLogin(Login $login): ?DomainIndividual
    {
        $value = $login->value();
        if ($value === null) {
            return null;
        }
        $eloquentPerson = $this->modelClass::where('login', $value)->first();

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

        if (isset($filters['creator_uid'])) {
            $query->where('creator_uid', $filters['creator_uid']);
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
        $eloquentPerson = $this->modelClass::query()->where('uid', $person->uid()->value())->first();
        if (!$eloquentPerson) {
            $eloquentPerson = new $this->modelClass();
            $eloquentPerson->uid = $person->uid()->value();
        }

        $eloquentPerson->fill([
            'first_name' => $person->name()->first(),
            'last_name' => $person->name()->last(),
            'middle_name' => $person->name()->middle(),
            'position_id' => $person->positionId(),
            'status_id' => $person->status()->value(),
            'login' => $person->login()->value(),
            'is_company_employee' => $person->isCompanyEmployee(),
            'creator_uid' => $person->creatorUid() ? $person->creatorUid()->value() : null,
        ]);
        $eloquentPerson->created_at = $person->createdAt();

        $eloquentPerson->save();
        return $person;
    }

    public function delete(PersonUid $uid): bool
    {
        $eloquentPerson = $this->modelClass::query()->where('uid', $uid->value())->first();

        return $eloquentPerson ? $eloquentPerson->delete() : false;
    }

    public function existsByLogin(Login $login): bool
    {
        $value = $login->value();
        if ($value === null) {
            return false;
        }
        return $this->modelClass::where('login', $value)->exists();
    }

    public function findCompanyEmployees(): array
    {
        $eloquentPersons = $this->modelClass::companyEmployees()->get();

        return $eloquentPersons->map(fn($person) => $this->toDomainModel($person))->toArray();
    }

    public function findByCreator(PersonUid $creatorUid): array
    {
        $eloquentPersons = $this->modelClass::byCreator($creatorUid->value())->get();

        return $eloquentPersons->map(fn($person) => $this->toDomainModel($person))->toArray();
    }

    public function findByStatus(PersonStatus $status): array
    {
        $eloquentPersons = $this->modelClass::byStatus($status->value())->get();

        return $eloquentPersons->map(fn($person) => $this->toDomainModel($person))->toArray();
    }

    private function toDomainModel(EloquentIndividual $eloquentIndividual): DomainIndividual
    {
        $domainPerson = new DomainIndividual(
            name: new Name(
                $eloquentIndividual->first_name,
                $eloquentIndividual->last_name,
                $eloquentIndividual->middle_name
            ),
            status: new PersonStatus($eloquentIndividual->status_id),
            creatorUid: $eloquentIndividual->creator_uid ? new PersonUid((string) $eloquentIndividual->creator_uid) : null,
            positionId: $eloquentIndividual->position_id,
            login: new Login($eloquentIndividual->login),
            isCompanyEmployee: (bool) $eloquentIndividual->is_company_employee,
            uid: new PersonUid((string) $eloquentIndividual->uid)
        );

        $domainPerson->setCreatedAt(Carbon::parse($eloquentIndividual->created_at));

        return $domainPerson;
    }
}
