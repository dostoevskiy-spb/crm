<?php

declare(strict_types=1);

namespace App\Modules\LegalEntity\Domain\Models;

use App\Modules\LegalEntity\Domain\ValueObjects\Id;
use App\Modules\User\Domain\ValueObjects\Id as UserId;
use App\Modules\LegalEntity\Domain\ValueObjects\Name;
use App\Modules\LegalEntity\Domain\ValueObjects\TaxNumber;
use DateTimeImmutable;

class LegalEntity
{
    private Id $uid;

    private Name $name;

    private TaxNumber $taxNumber;

    private ?string $legalAddress = null;

    private ?string $phoneNumber = null;

    private ?string $email = null;

    private DateTimeImmutable $createdAt;

    private ?UserId $creatorUid = null;

    private ?UserId $curatorUid = null;

    public function __construct(
        Name $name,
        TaxNumber $taxNumber,
        ?UserId $creatorUid = null,
        ?Id $uid = null
    ) {
        $this->name = $name;
        $this->taxNumber = $taxNumber;
        $this->creatorUid = $creatorUid;
        $this->createdAt = new DateTimeImmutable;
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

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function creatorUid(): ?UserId
    {
        return $this->creatorUid;
    }

    public function curatorUid(): ?UserId
    {
        return $this->curatorUid;
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

    public function setCuratorUid(?UserId $curatorUid): void
    {
        $this->curatorUid = $curatorUid;
    }
}
