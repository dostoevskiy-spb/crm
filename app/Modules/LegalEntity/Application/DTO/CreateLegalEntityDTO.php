<?php

declare(strict_types=1);

namespace App\Modules\LegalEntity\Application\DTO;

final readonly class CreateLegalEntityDTO
{
    public function __construct(
        public string $shortName,
        public string $fullName,
        public string $ogrn,
        public string $inn,
        public string $kpp,
        public ?string $legalAddress = null,
        public ?string $phoneNumber = null,
        public ?string $email = null,
        public ?string $creatorUid = null
    ) {}
}
