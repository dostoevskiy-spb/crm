<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Contracts;

use App\Modules\User\Domain\Models\User;
use App\Modules\User\Domain\ValueObjects\EmailAddress;
use App\Modules\User\Domain\ValueObjects\Id;

interface UserRepositoryInterface
{
    public function findByUid(Id $uid): ?User;

    public function findByEmail(EmailAddress $email): ?User;

    /** @return User[] */
    public function findAll(): array;

    /** @return User[] */
    public function findByFilters(array $filters): array;

    public function save(User $user): User;

    public function delete(Id $uid): bool;

    public function existsByEmail(EmailAddress $email): bool;
}
