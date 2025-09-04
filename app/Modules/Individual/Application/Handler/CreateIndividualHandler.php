<?php

declare(strict_types=1);

namespace App\Modules\Individual\Application\Handler;

use App\Modules\Individual\Application\Command\CreateIndividualCommand;
use App\Modules\Individual\Domain\Contracts\IndividualRepositoryInterface;
use App\Modules\Individual\Domain\Enums\StatusEnum;
use App\Modules\Individual\Domain\Models\Individual;
use App\Modules\Individual\Domain\ValueObjects\Id;
use App\Modules\Individual\Domain\ValueObjects\Login;
use App\Modules\Individual\Domain\ValueObjects\Name;

final readonly class CreateIndividualHandler
{
    public function __construct(
        private IndividualRepositoryInterface $repository
    ) {}

    public function __invoke(CreateIndividualCommand $command): string
    {
        $dto = $command->dto;

        if ($dto->login !== null && $dto->login !== '' && $this->repository->existsByLogin(new Login($dto->login))) {
            throw new \InvalidArgumentException('Login already exists');
        }

        $name = new Name($dto->firstName, $dto->lastName, $dto->middleName);
        $status = StatusEnum::tryFrom($dto->status);
        $creatorUid = $dto->creatorUid ? new Id($dto->creatorUid) : null;
        $login = new Login($dto->login);

        $individual = new Individual(
            name: $name,
            status: $status,
            creatorUid: $creatorUid,
            positionId: $dto->positionId,
            login: $login,
            isCompanyEmployee: $dto->isCompanyEmployee
        );

        $saved = $this->repository->save($individual);

        return $saved->id()->value();
    }
}
