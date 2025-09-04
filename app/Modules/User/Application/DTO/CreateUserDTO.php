<?php

declare(strict_types=1);

namespace App\Modules\User\Application\DTO;

final readonly class CreateUserDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $status = 'active'
    ) {}
}
