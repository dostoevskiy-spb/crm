<?php

declare(strict_types=1);

namespace App\Modules\LegalEntity\Domain\Models;

use App\Modules\Individual\Domain\ValueObjects\Id;
use App\Modules\LegalEntity\Domain\ValueObjects\Name;
use App\Modules\LegalEntity\Domain\ValueObjects\Id;
use App\Modules\LegalEntity\Domain\ValueObjects\TaxNumber;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'legal_entity')]
class LegalEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $uid;

    #[ORM\Embedded(class: Name::class, columnPrefix: 'name_')]
    private Name $name;

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
        Name $name,
        TaxNumber $taxNumber,
        ?Id $creatorUid = null,
        ?Id $uid = null
    ) {
        $this->name = $name;
        $this->taxNumber = $taxNumber;
        $this->creatorUid = $creatorUid?->value();
        $this->createdAt = new \DateTimeImmutable;
        $this->uid = $uid?->value() ?? $this->generateUuid();
    }

    private function generateUuid(): string
    {
        if (function_exists('uuid_create')) {
            return (string) uuid_create(UUID_TYPE_RANDOM);
        }
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0F) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3F) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function uid(): Id
    {
        return new Id($this->uid);
    }

    public function name(): Name
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

    public function creatorUid(): ?Id
    {
        return $this->creatorUid ? new Id($this->creatorUid) : null;
    }

    public function curatorUid(): ?Id
    {
        return $this->curatorUid ? new Id($this->curatorUid) : null;
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

    public function setCuratorUid(?Id $curatorUid): void
    {
        $this->curatorUid = $curatorUid?->value();
    }
}
