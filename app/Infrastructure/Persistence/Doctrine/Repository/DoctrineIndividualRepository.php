<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Individual\Contracts\IndividualRepositoryInterface;
use App\Domain\Individual\Models\Individual;
use App\Domain\Individual\ValueObjects\Login;
use App\Domain\Individual\ValueObjects\PersonStatus;
use App\Domain\Individual\ValueObjects\PersonUid;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineIndividualRepository implements IndividualRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findByUid(PersonUid $uid): ?Individual
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

        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $qb->andWhere(
                $qb->expr()->orX(
                    'i.name.firstName LIKE :term',
                    'i.name.lastName LIKE :term',
                    'i.name.middleName LIKE :term'
                )
            )->setParameter('term', $term);
        }

        if (isset($filters['status_id'])) {
            $qb->andWhere('i.statusId = :statusId')
               ->setParameter('statusId', (int) $filters['status_id']);
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

    public function delete(PersonUid $uid): bool
    {
        $entity = $this->findByUid($uid);
        if (!$entity) {
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

    public function findByCreator(PersonUid $creatorUid): array
    {
        return $this->em->getRepository(Individual::class)
            ->findBy(['creatorUid' => $creatorUid->value()]);
    }

    public function findByStatus(PersonStatus $status): array
    {
        return $this->em->getRepository(Individual::class)
            ->findBy(['statusId' => $status->value()]);
    }
}
