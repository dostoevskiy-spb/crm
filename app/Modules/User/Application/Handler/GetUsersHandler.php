<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Handler;

use App\Modules\User\Application\Query\GetUsersQuery;
use App\Modules\User\Domain\Contracts\UserRepositoryInterface;

final readonly class GetUsersHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function __invoke(GetUsersQuery $query): array
    {
        $users = $this->userRepository->findByFilters($query->filters);

        return array_map(function ($user) {
            return [
                'uid' => $user->uid()->value(),
                'email' => $user->email()->value(),
                'status' => $user->status()->value(),
                'createdAt' => $user->createdAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $user->updatedAt()?->format('Y-m-d H:i:s'),
                'lastLoginAt' => $user->lastLoginAt()?->format('Y-m-d H:i:s'),
            ];
        }, $users);
    }
}
