<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Handler;

use App\Modules\User\Application\Command\CreateUserCommand;
use App\Modules\User\Domain\Contracts\UserRepositoryInterface;
use App\Modules\User\Domain\Enum\StatusEnum;
use App\Modules\User\Domain\Models\User;
use App\Modules\User\Domain\ValueObjects\EmailAddress;

final readonly class CreateUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function __invoke(CreateUserCommand $command): string
    {
        $dto = $command->dto;

        $email = new EmailAddress($dto->email);
        if ($this->userRepository->existsByEmail($email)) {
            throw new \InvalidArgumentException('User with this email already exists');
        }

        $status = StatusEnum::tryFrom($dto->status) ?? StatusEnum::ACTIVE;

        $passwordHash = password_hash($dto->password, PASSWORD_BCRYPT);
        if ($passwordHash === false) {
            throw new \RuntimeException('Failed to hash password');
        }

        $user = new User($email, $passwordHash, $status);
        $saved = $this->userRepository->save($user);

        return $saved->uid()->value();
    }
}
