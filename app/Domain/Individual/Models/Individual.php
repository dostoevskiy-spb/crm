<?php

declare(strict_types=1);

namespace App\Domain\Individual\Models;

use App\Domain\Individual\Entities\ContactInfo;
use App\Domain\Individual\ValueObjects\Login;
use App\Domain\Individual\ValueObjects\Name;
use App\Domain\Individual\ValueObjects\PersonStatus;
use App\Domain\Individual\ValueObjects\PersonUid;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

/**
 * Physical person domain entity
 */
#[ORM\Entity]
#[ORM\Table(name: 'individual')]
class Individual
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $uid;

    #[ORM\Embedded(class: Name::class, columnPrefix: false)]
    private Name $name;

    #[ORM\Column(name: 'position_id', type: 'integer', nullable: true)]
    private ?int $positionId = null;

    #[ORM\Column(name: 'status_id', type: 'integer')]
    private int $statusId;

    #[ORM\Column(name: 'login', type: 'string', length: 50, nullable: true)]
    private ?string $loginValue = null;

    #[ORM\Column(name: 'is_company_employee', type: 'boolean')]
    private bool $isCompanyEmployee = false;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: 'creator_uid', type: 'guid', nullable: true)]
    private ?string $creatorUid = null;
    /** @var ContactInfo[] */
    private array $contacts = [];

    public function __construct(
        Name $name,
        PersonStatus $status,
        ?PersonUid $creatorUid = null,
        ?int $positionId = null,
        ?Login $login = null,
        bool $isCompanyEmployee = false,
        ?PersonUid $uid = null
    ) {
        $this->name = $name;
        $this->statusId = $status->value();
        $this->creatorUid = $creatorUid?->value();
        $this->positionId = $positionId;
        $this->loginValue = $login?->value();
        $this->isCompanyEmployee = $isCompanyEmployee;
        $this->createdAt = Carbon::now();
        $this->uid = $uid?->value() ?? self::generateUuid();
    }

    private static function generateUuid(): string
    {
        // Use PHP uuid generation via ramsey/uuid if available; fallback to random bytes
        if (function_exists('uuid_create')) {
            return (string) uuid_create(UUID_TYPE_RANDOM);
        }
        $data = random_bytes(16);
        // Set version to 4
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        // Set variant to RFC 4122
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function uid(): PersonUid { return new PersonUid($this->uid); }
    public function name(): Name { return $this->name; }
    public function positionId(): ?int { return $this->positionId; }
    public function status(): PersonStatus { return new PersonStatus($this->statusId); }
    public function login(): Login { return new Login($this->loginValue); }
    public function isCompanyEmployee(): bool { return $this->isCompanyEmployee; }
    public function createdAt(): Carbon {
        if ($this->createdAt instanceof Carbon) {
            return $this->createdAt;
        }
        if ($this->createdAt instanceof \DateTimeImmutable) {
            $mutable = \DateTime::createFromImmutable($this->createdAt);
            return Carbon::instance($mutable);
        }
        return Carbon::instance($this->createdAt);
    }
    public function creatorUid(): ?PersonUid { return $this->creatorUid ? new PersonUid($this->creatorUid) : null; }

    public function setPositionId(?int $positionId): void { $this->positionId = $positionId; }
    public function setStatus(PersonStatus $status): void { $this->statusId = $status->value(); }
    public function setLogin(Login $login): void { $this->loginValue = $login->value(); }
    public function setIsCompanyEmployee(bool $isCompanyEmployee): void { $this->isCompanyEmployee = $isCompanyEmployee; }
    public function setCreatedAt(Carbon $createdAt): void { $this->createdAt = $createdAt; }

    public function getFirstName(): string { return $this->name->first(); }
    public function getLastName(): string { return $this->name->last(); }
    public function getMiddleName(): string { return $this->name->middle(); }
    public function getFullName(): string { return $this->name->full(); }
    public function getShortName(): string { return $this->name->short(); }
    public function hasLogin(): bool { return !(new Login($this->loginValue))->isEmpty(); }
    public function getLogin(): ?string { return (new Login($this->loginValue))->value(); }
    public function getStatusId(): int { return $this->statusId; }

    /**
     * Contact management (in-aggregate, persistence TBD).
     */
    public function addContact(ContactInfo $contact): void
    {
        if ($contact->isPrimary()) {
            foreach ($this->contacts as $c) {
                if ($c->isPrimary()) {
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
            if ($c->isPrimary()) {
                return $c;
            }
        }
        return null;
    }

    public function setPrimaryContact(int $index): void
    {
        if (!isset($this->contacts[$index])) {
            throw new \OutOfBoundsException('Contact index out of bounds');
        }
        foreach ($this->contacts as $i => $c) {
            $c->markPrimary($i === $index);
        }
    }
}
