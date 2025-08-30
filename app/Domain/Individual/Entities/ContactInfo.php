<?php

declare(strict_types=1);

namespace App\Domain\Individual\Entities;

use App\Domain\Individual\ValueObjects\EmailAddress;
use App\Domain\Individual\ValueObjects\PersonUid;
use App\Domain\Individual\ValueObjects\PhoneNumber;
use Carbon\CarbonImmutable;

final class ContactInfo
{
    private ?EmailAddress $email;
    private ?PhoneNumber $phone;
    private bool $isPrimary;
    private bool $hasTelegram;
    private bool $hasWhatsUp;
    private PersonUid $addedBy;
    private ?PersonUid $editedBy;
    private CarbonImmutable $addedAt;
    private ?CarbonImmutable $editedAt;

    public function __construct(
        ?PhoneNumber $phone,
        ?EmailAddress $email,
        bool $isPrimary,
        bool $hasTelegram,
        bool $hasWhatsUp,
        PersonUid $addedBy,
        ?PersonUid $editedBy = null,
        ?CarbonImmutable $addedAt = null,
        ?CarbonImmutable $editedAt = null,
    ) {
        $this->phone = $phone;
        $this->email = $email;
        $this->isPrimary = $isPrimary;
        $this->hasTelegram = $hasTelegram;
        $this->hasWhatsUp = $hasWhatsUp;
        $this->addedBy = $addedBy;
        $this->editedBy = $editedBy;
        $this->addedAt = $addedAt ?? CarbonImmutable::now();
        $this->editedAt = $editedAt;
    }

    public function phone(): ?PhoneNumber { return $this->phone; }
    public function email(): ?EmailAddress { return $this->email; }
    public function isPrimary(): bool { return $this->isPrimary; }
    public function hasTelegram(): bool { return $this->hasTelegram; }
    public function hasWhatsUp(): bool { return $this->hasWhatsUp; }

    public function markPrimary(bool $value): void { $this->isPrimary = $value; }

    public function update(?PhoneNumber $phone, ?EmailAddress $email, bool $hasTelegram, bool $hasWhatsUp, PersonUid $editor): void
    {
        $this->phone = $phone;
        $this->email = $email;
        $this->hasTelegram = $hasTelegram;
        $this->hasWhatsUp = $hasWhatsUp;
        $this->editedBy = $editor;
        $this->editedAt = CarbonImmutable::now();
    }

    public function addedAt(): CarbonImmutable { return $this->addedAt; }
    public function editedAt(): ?CarbonImmutable { return $this->editedAt; }
}
