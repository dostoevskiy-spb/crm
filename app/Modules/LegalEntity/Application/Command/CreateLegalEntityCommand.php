<?php

declare(strict_types=1);

namespace App\Modules\LegalEntity\Application\Command;

use App\Modules\LegalEntity\Application\DTO\CreateLegalEntityDTO;

final readonly class CreateLegalEntityCommand
{
    public function __construct(public CreateLegalEntityDTO $dto) {}
}
