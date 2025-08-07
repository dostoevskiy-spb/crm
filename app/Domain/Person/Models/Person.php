<?php

declare(strict_types=1);

namespace App\Domain\Person\Models;

use Carbon\Carbon;

/**
 * Physical person domain entity
 */
class Person
{
    private ?int $id = null;
    private string $firstName;
    private string $lastName;
    private string $middleName;
    private ?int $positionId = null;
    private int $statusId;
    private ?string $login = null;
    private bool $isCompanyEmployee = false;
    private Carbon $createdAt;
    private int $creatorId;
    
    public function __construct(
        string $firstName,
        string $lastName,
        string $middleName,
        int $statusId,
        int $creatorId,
        ?int $positionId = null,
        ?string $login = null,
        bool $isCompanyEmployee = false
    ) {
        $this->setFirstName($firstName);
        $this->setLastName($lastName);
        $this->setMiddleName($middleName);
        $this->statusId = $statusId;
        $this->creatorId = $creatorId;
        $this->positionId = $positionId;
        $this->login = $login;
        $this->isCompanyEmployee = $isCompanyEmployee;
        $this->createdAt = Carbon::now();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    
    public function getFirstName(): string
    {
        return $this->firstName;
    }
    
    public function setFirstName(string $firstName): void
    {
        if (empty(trim($firstName)) || strlen($firstName) > 20) {
            throw new \InvalidArgumentException('First name must be between 1 and 20 characters');
        }
        $this->firstName = trim($firstName);
    }
    
    public function getLastName(): string
    {
        return $this->lastName;
    }
    
    public function setLastName(string $lastName): void
    {
        if (empty(trim($lastName)) || strlen($lastName) > 20) {
            throw new \InvalidArgumentException('Last name must be between 1 and 20 characters');
        }
        $this->lastName = trim($lastName);
    }
    
    public function getMiddleName(): string
    {
        return $this->middleName;
    }
    
    public function setMiddleName(string $middleName): void
    {
        if (empty(trim($middleName)) || strlen($middleName) > 20) {
            throw new \InvalidArgumentException('Middle name must be between 1 and 20 characters');
        }
        $this->middleName = trim($middleName);
    }
    
    public function getFullName(): string
    {
        return "{$this->lastName} {$this->firstName} {$this->middleName}";
    }
    
    public function getShortName(): string
    {
        return "{$this->lastName} {$this->firstName[0]}.{$this->middleName[0]}.";
    }
    
    public function getPositionId(): ?int
    {
        return $this->positionId;
    }
    
    public function setPositionId(?int $positionId): void
    {
        $this->positionId = $positionId;
    }
    
    public function getStatusId(): int
    {
        return $this->statusId;
    }
    
    public function setStatusId(int $statusId): void
    {
        $this->statusId = $statusId;
    }
    
    public function getLogin(): ?string
    {
        return $this->login;
    }
    
    public function setLogin(?string $login): void
    {
        if ($login !== null && strlen($login) < 6) {
            throw new \InvalidArgumentException('Login must be at least 6 characters long');
        }
        $this->login = $login;
    }
    
    public function isCompanyEmployee(): bool
    {
        return $this->isCompanyEmployee;
    }
    
    public function setIsCompanyEmployee(bool $isCompanyEmployee): void
    {
        $this->isCompanyEmployee = $isCompanyEmployee;
    }
    
    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }
    
    public function setCreatedAt(Carbon $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
    
    public function getCreatorId(): int
    {
        return $this->creatorId;
    }
    
    public function setCreatorId(int $creatorId): void
    {
        $this->creatorId = $creatorId;
    }
    
    public function hasLogin(): bool
    {
        return !empty($this->login);
    }
    
    public function canAccessSystem(): bool
    {
        return $this->hasLogin() && $this->isCompanyEmployee;
    }
}
