<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Models;

use App\Modules\User\Domain\Enum\StatusEnum;
use App\Modules\User\Domain\ValueObjects\EmailAddress;
use App\Modules\User\Domain\ValueObjects\Id;
use DateTimeImmutable;

final class User
{
    private Id $uid;

    private string $emailValue;

    private StatusEnum $status;

    private string $passwordHash;

    private DateTimeImmutable $createdAt;

    private ?DateTimeImmutable $updatedAt = null;

    private ?DateTimeImmutable $lastLoginAt = null;

    public function __construct(
        EmailAddress $email,
        string $passwordHash,
        StatusEnum $status = StatusEnum::ACTIVE,
        ?Id $uid = null
    ) {
        $this->uid = $uid ?? Id::next();
        $this->emailValue = $email->value();
        $this->passwordHash = $passwordHash;
        $this->status = $status;
        $this->createdAt = new DateTimeImmutable;
    }

    public function uid(): Id
    {
        return $this->uid;
    }

    public function email(): EmailAddress
    {
        return new EmailAddress($this->emailValue);
    }

    public function status(): StatusEnum
    {
        return $this->status;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function lastLoginAt(): ?DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setEmail(EmailAddress $email): void
    {
        $this->emailValue = $email->value();
        $this->touch();
    }

    public function setStatus(StatusEnum $status): void
    {
        $this->status = $status;
        $this->touch();
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
        $this->touch();
    }

    public function setLastLoginAt(?DateTimeImmutable $at): void
    {
        $this->lastLoginAt = $at;
        $this->touch();
    }

    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable;
    }
}
