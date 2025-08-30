<?php

declare(strict_types=1);

namespace App\Application\LegalEntity\Command;

use App\Application\LegalEntity\DTO\CreateLegalEntityDTO;

final readonly class CreateLegalEntityCommand
{
    public function __construct(public CreateLegalEntityDTO $dto) {}
}
