<?php

declare(strict_types=1);

namespace App\Domain\LegalEntity\Models;

use App\Domain\Individual\ValueObjects\EmailAddress;
use App\Domain\Individual\ValueObjects\PersonUid;
use App\Domain\Individual\ValueObjects\PhoneNumber;
use App\Domain\LegalEntity\ValueObjects\CompanyName;
use App\Domain\LegalEntity\ValueObjects\LegalEntityUid;
use App\Domain\LegalEntity\ValueObjects\TaxNumber;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'legal_entity')]
class LegalEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $uid;

    #[ORM\Embedded(class: CompanyName::class, columnPrefix: 'name_')]
    private CompanyName $name;

    #[ORM\Embedded(class: TaxNumber::class, columnPrefix: 'tax_')]
    private TaxNumber $taxNumber;

    #[ORM\Column(name: 'legal_address', type: 'text', nullable: true)]
    private ?string $legalAddress = null;

    #[ORM\Column(name: 'phone_number', type: 'string', length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'creator_uid', type: 'guid', nullable: true)]
    private ?string $creatorUid = null;

    #[ORM\Column(name: 'curator_uid', type: 'guid', nullable: true)]
    private ?string $curatorUid = null;

    public function __construct(
        CompanyName $name,
        TaxNumber $taxNumber,
        ?PersonUid $creatorUid = null,
        ?LegalEntityUid $uid = null
    ) {
        $this->name = $name;
        $this->taxNumber = $taxNumber;
        $this->creatorUid = $creatorUid?->value();
        $this->createdAt = new \DateTimeImmutable();
        $this->uid = $uid?->value() ?? $this->generateUuid();
    }

    private function generateUuid(): string
    {
        if (function_exists('uuid_create')) {
            return (string) uuid_create(UUID_TYPE_RANDOM);
        }
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function uid(): LegalEntityUid
    {
        return new LegalEntityUid($this->uid);
    }

    public function name(): CompanyName
    {
        return $this->name;
    }

    public function taxNumber(): TaxNumber
    {
        return $this->taxNumber;
    }

    public function legalAddress(): ?string
    {
        return $this->legalAddress;
    }

    public function phoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function creatorUid(): ?PersonUid
    {
        return $this->creatorUid ? new PersonUid($this->creatorUid) : null;
    }

    public function curatorUid(): ?PersonUid
    {
        return $this->curatorUid ? new PersonUid($this->curatorUid) : null;
    }

    public function setLegalAddress(?string $legalAddress): void
    {
        $this->legalAddress = $legalAddress;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function setCuratorUid(?PersonUid $curatorUid): void
    {
        $this->curatorUid = $curatorUid?->value();
    }
}
