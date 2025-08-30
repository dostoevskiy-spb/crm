<?php

declare(strict_types=1);

namespace App\Application\Individual\Handler;

use App\Application\Individual\Command\CreateIndividualCommand;
use App\Domain\Individual\Contracts\IndividualRepositoryInterface;
use App\Domain\Individual\Models\Individual;
use App\Domain\Individual\ValueObjects\Login;
use App\Domain\Individual\ValueObjects\Name;
use App\Domain\Individual\ValueObjects\PersonStatus;
use App\Domain\Individual\ValueObjects\PersonUid;

final class CreateIndividualHandler
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
        $status = new PersonStatus($dto->statusId);
        $creatorUid = $dto->creatorUid ? new PersonUid($dto->creatorUid) : null;
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
        return $saved->uid()->value();
    }
}
