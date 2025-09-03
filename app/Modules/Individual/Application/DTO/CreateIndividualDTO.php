<?php

declare(strict_types=1);

namespace App\Modules\Individual\Application\DTO;

final readonly class CreateIndividualDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $middleName,
        public string $status,
        public ?int $positionId = null,
        public ?string $login = null,
        public bool $isCompanyEmployee = false,
        public ?string $creatorUid = null,
    ) {}
}
