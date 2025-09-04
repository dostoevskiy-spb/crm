<?php

declare(strict_types=1);

namespace App\Modules\User\Infrastructure\Persistence\Doctrine\Repository;

use App\Modules\User\Domain\Contracts\UserRepositoryInterface;
use App\Modules\User\Domain\Models\User;
use App\Modules\User\Domain\ValueObjects\EmailAddress;
use App\Modules\User\Domain\ValueObjects\Id;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findByUid(Id $uid): ?User
    {
        return $this->em->find(User::class, $uid->value());
    }

    public function findByEmail(EmailAddress $email): ?User
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from(User::class, 'u')
            ->andWhere('u.emailValue = :email')
            ->setParameter('email', $email->value())
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAll(): array
    {
        return $this->em->getRepository(User::class)->findAll();
    }

    public function findByFilters(array $filters): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')->from(User::class, 'u');

        if (isset($filters['status']) && $filters['status'] !== '') {
            $qb->andWhere('u.status = :status')
                ->setParameter('status', (string) $filters['status']);
        }
        if (isset($filters['email']) && $filters['email'] !== '') {
            $term = '%'.$filters['email'].'%';
            $qb->andWhere('u.emailValue LIKE :email')
                ->setParameter('email', $term);
        }

        return $qb->getQuery()->getResult();
    }

    public function save(User $user): User
    {
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function delete(Id $uid): bool
    {
        $entity = $this->findByUid($uid);
        if (! $entity) {
            return false;
        }
        $this->em->remove($entity);
        $this->em->flush();

        return true;
    }

    public function existsByEmail(EmailAddress $email): bool
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('COUNT(u.uid)')
            ->from(User::class, 'u')
            ->andWhere('u.emailValue = :email')
            ->setParameter('email', $email->value());

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
