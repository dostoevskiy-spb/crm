<?php

declare(strict_types=1);

namespace App\Modules\Individual\Application\Command;

use App\Modules\Individual\Application\DTO\CreateIndividualDTO;

final readonly class CreateIndividualCommand
{
    public function __construct(public CreateIndividualDTO $dto) {}
}
