<?php

declare(strict_types=1);

namespace App\Modules\LegalEntity\Domain\Models;

use App\Modules\Individual\Domain\ValueObjects\Id;
use App\Modules\LegalEntity\Domain\ValueObjects\Name;
use App\Modules\LegalEntity\Domain\ValueObjects\TaxNumber;

class LegalEntity
{
    private Id $uid;

    private Name $name;

    private TaxNumber $taxNumber;

    private ?string $legalAddress = null;

    private ?string $phoneNumber = null;

    private ?string $email = null;

    private \DateTimeImmutable $createdAt;

    private ?string $creatorUid = null;

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
        $this->uid = $uid ?? Id::next();
    }

    public function uid(): Id
    {
        return $this->uid;
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
