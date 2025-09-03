<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManagerInterface;

final class Transactional
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * @template T
     *
     * @param  callable():T  $fn
     * @return T
     */
    public function run(callable $fn): mixed
    {
        return $this->em->wrapInTransaction($fn);
    }
}
