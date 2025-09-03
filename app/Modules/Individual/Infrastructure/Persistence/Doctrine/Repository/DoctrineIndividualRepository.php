<?php

declare(strict_types=1);

namespace App\Modules\Individual\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Individual\ValueObjects\PersonStatus;
use App\Modules\Individual\Domain\Contracts\IndividualRepositoryInterface;
use App\Modules\Individual\Domain\Enums\StatusEnum;
use App\Modules\Individual\Domain\Models\Individual;
use App\Modules\Individual\Domain\ValueObjects\Id;
use App\Modules\Individual\Domain\ValueObjects\Login;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

final class DoctrineIndividualRepository implements IndividualRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function findByUid(Id $uid): ?Individual
    {
        return $this->em->find(Individual::class, $uid->value());
    }

    public function findByLogin(Login $login): ?Individual
    {
        $value = $login->value();
        if ($value === null) {
            return null;
        }

        $qb = $this->em->createQueryBuilder();
        $qb->select('i')
            ->from(Individual::class, 'i')
            ->andWhere('i.loginValue = :login')
            ->setParameter('login', $value)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAll(): array
    {
        return $this->em->getRepository(Individual::class)->findAll();
    }

    public function findByFilters(array $filters): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('i')
            ->from(Individual::class, 'i');

        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $qb->andWhere(
                $qb->expr()->orX(
                    'i.name.firstName LIKE :term',
                    'i.name.lastName LIKE :term',
                    'i.name.middleName LIKE :term'
                )
            )->setParameter('term', $term);
        }

        if (isset($filters['status'])) {
            $status = is_string($filters['status']) ? StatusEnum::tryFrom($filters['status']) : null;
            if ($status) {
                $code = match ($status) {
                    StatusEnum::ACTIVE => 1,
                    StatusEnum::ARCHIVED => 2,
                };
                $qb->andWhere('i.status = :status')
                    ->setParameter('status', $code);
            }
        }

        if (isset($filters['creator_uid'])) {
            $qb->andWhere('i.creatorUid = :creatorUid')
                ->setParameter('creatorUid', $filters['creator_uid']);
        }

        if (isset($filters['is_company_employee'])) {
            $qb->andWhere('i.isCompanyEmployee = :ice')
                ->setParameter('ice', (bool) $filters['is_company_employee']);
        }

        if (isset($filters['has_login'])) {
            if ($filters['has_login']) {
                $qb->andWhere('i.loginValue IS NOT NULL');
            } else {
                $qb->andWhere('i.loginValue IS NULL');
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function save(Individual $person): Individual
    {
        $this->em->persist($person);
        $this->em->flush();

        return $person;
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

    public function existsByLogin(Login $login): bool
    {
        $value = $login->value();
        if ($value === null) {
            return false;
        }

        $qb = $this->em->createQueryBuilder();
        $qb->select('COUNT(i.uid)')
            ->from(Individual::class, 'i')
            ->andWhere('i.loginValue = :login')
            ->setParameter('login', $value);

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function findCompanyEmployees(): array
    {
        return $this->em->getRepository(Individual::class)
            ->findBy(['isCompanyEmployee' => true]);
    }

    public function findByCreator(Id $creatorUid): array
    {
        return $this->em->getRepository(Individual::class)
            ->findBy(['creatorUid' => $creatorUid->value()]);
    }

    public function findByStatus(StatusEnum $status): array
    {
        $code = match ($status) {
            StatusEnum::ACTIVE => 1,
            StatusEnum::ARCHIVED => 2,
        };
        return $this->em->getRepository(Individual::class)
            ->findBy(['status' => $code]);
    }
}

