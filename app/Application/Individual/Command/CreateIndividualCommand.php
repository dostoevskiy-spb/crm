<?php

declare(strict_types=1);

namespace App\Application\Individual\Command;

use App\Application\Individual\DTO\CreateIndividualDTO;

final readonly class CreateIndividualCommand
{
    public function __construct(public CreateIndividualDTO $dto) {}
}
