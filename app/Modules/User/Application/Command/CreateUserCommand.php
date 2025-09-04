<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Command;

use App\Modules\User\Application\DTO\CreateUserDTO;

final readonly class CreateUserCommand
{
    public function __construct(public CreateUserDTO $dto) {}
}
