<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Handler;

use App\Modules\User\Application\Query\GetUserQuery;
use App\Modules\User\Domain\Contracts\UserRepositoryInterface;
use App\Modules\User\Domain\ValueObjects\Id;

final class GetUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function __invoke(GetUserQuery $query): ?array
    {
        $uid = new Id($query->uid);
        $user = $this->userRepository->findByUid($uid);

        if (! $user) {
            return null;
        }

        return [
            'uid' => $user->uid()->value(),
            'email' => $user->email()->value(),
            'status' => $user->status()->value(),
            'createdAt' => $user->createdAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $user->updatedAt()?->format('Y-m-d H:i:s'),
            'lastLoginAt' => $user->lastLoginAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
