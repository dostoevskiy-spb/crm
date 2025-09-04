<?php

declare(strict_types=1);

namespace App\Modules\Individual\Domain\Models;

use App\Modules\Individual\Domain\Entities\ContactInfo;
use App\Modules\Individual\Domain\Enums\StatusEnum;
use App\Modules\Individual\Domain\ValueObjects\Id;
use App\Modules\Individual\Domain\ValueObjects\Login;
use App\Modules\Individual\Domain\ValueObjects\Name;
use Carbon\Carbon;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Physical person domain entity
 */
class Individual
{
    private Id $id;

    private Name $name;

    public ?int $positionId = null;

    private StatusEnum $status;

    #[ORM\Column(name: 'login', type: 'string', length: 50, nullable: true)]
    private ?string $loginValue = null;

    #[ORM\Column(name: 'is_company_employee', type: 'boolean')]
    private bool $isCompanyEmployee = false;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private DateTimeInterface $createdAt;

    #[ORM\Column(name: 'creator_uid', type: 'guid', nullable: true)]
    private ?string $creatorUid = null;

    /** @var ContactInfo[] */
    private array $contacts = [];

    public function __construct(
        Name $name,
        StatusEnum $status,
        ?Id $creatorUid = null,
        ?int $positionId = null,
        ?Login $login = null,
        bool $isCompanyEmployee = false,
        ?Id $uid = null
    ) {
        $this->id = $uid ?? Id::next()->value();
        $this->name = $name;
        $this->status = $status;
        $this->creatorUid = $creatorUid?->value();
        $this->positionId = $positionId;
        $this->loginValue = $login?->value();
        $this->isCompanyEmployee = $isCompanyEmployee;
        $this->createdAt = Carbon::now();
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function positionId(): ?int
    {
        return $this->positionId;
    }

    public function status(): StatusEnum
    {
        return $this->status;
    }

    public function login(): Login
    {
        return new Login($this->loginValue);
    }

    public function isCompanyEmployee(): bool
    {
        return $this->isCompanyEmployee;
    }

    public function createdAt(): Carbon
    {
        if ($this->createdAt instanceof Carbon) {
            return $this->createdAt;
        }
        if ($this->createdAt instanceof \DateTimeImmutable) {
            $mutable = \DateTime::createFromImmutable($this->createdAt);

            return Carbon::instance($mutable);
        }

        return Carbon::instance($this->createdAt);
    }

    public function creatorUid(): ?Id
    {
        return $this->creatorUid ? new Id($this->creatorUid) : null;
    }

    public function setStatus(StatusEnum $status): void
    {
        $this->status = $status;
    }

    public function setLogin(Login $login): void
    {
        $this->loginValue = $login->value();
    }

    public function setIsCompanyEmployee(bool $isCompanyEmployee): void
    {
        $this->isCompanyEmployee = $isCompanyEmployee;
    }

    public function setCreatedAt(Carbon $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getFirstName(): string
    {
        return $this->name->first();
    }

    public function getLastName(): string
    {
        return $this->name->last();
    }

    public function getMiddleName(): string
    {
        return $this->name->middle();
    }

    public function getFullName(): string
    {
        return $this->name->full();
    }

    public function getShortName(): string
    {
        return $this->name->short();
    }

    public function hasLogin(): bool
    {
        return ! (new Login($this->loginValue))->isEmpty();
    }

    public function getLogin(): ?string
    {
        return (new Login($this->loginValue))->value();
    }

    /**
     * Contact management (in-aggregate, persistence TBD).
     */
    public function addContact(ContactInfo $contact): void
    {
        if ($contact->isPrimary) {
            foreach ($this->contacts as $c) {
                if ($c->isPrimary) {
                    $c->markPrimary(false);
                }
            }
        }
        $this->contacts[] = $contact;
    }

    /** @return ContactInfo[] */
    public function contacts(): array
    {
        return $this->contacts;
    }

    public function primaryContact(): ?ContactInfo
    {
        foreach ($this->contacts as $c) {
            if ($c->isPrimary) {
                return $c;
            }
        }

        return null;
    }

    public function setPrimaryContact(int $index): void
    {
        if (! isset($this->contacts[$index])) {
            throw new \OutOfBoundsException('Contact index out of bounds');
        }
        foreach ($this->contacts as $i => $c) {
            $c->markPrimary($i === $index);
        }
    }
}
