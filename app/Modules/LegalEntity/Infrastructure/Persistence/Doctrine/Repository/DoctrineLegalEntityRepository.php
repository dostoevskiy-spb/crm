<?php

declare(strict_types=1);

namespace App\Modules\LegalEntity\Infrastructure\Persistence\Doctrine\Repository;

use App\Modules\Individual\Domain\ValueObjects\Id;
use App\Modules\LegalEntity\Domain\Contracts\LegalEntityRepositoryInterface;
use App\Modules\LegalEntity\Domain\Models\LegalEntity;
use App\Modules\User\Domain\ValueObjects\Id as UserId;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineLegalEntityRepository implements LegalEntityRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findByUid(Id $uid): ?LegalEntity
    {
        return $this->em->find(LegalEntity::class, $uid->value());
    }

    public function findByInn(string $inn): ?LegalEntity
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('le')
            ->from(LegalEntity::class, 'le')
            ->andWhere('le.taxNumber.inn = :inn')
            ->setParameter('inn', $inn)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAll(): array
    {
        return $this->em->getRepository(LegalEntity::class)->findAll();
    }

    public function findByFilters(array $filters): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('le')
            ->from(LegalEntity::class, 'le');

        if (! empty($filters['shortName'])) {
            $qb->andWhere('le.name.shortName LIKE :shortName')
                ->setParameter('shortName', '%'.$filters['shortName'].'%');
        }

        if (! empty($filters['inn'])) {
            $qb->andWhere('le.taxNumber.inn = :inn')
                ->setParameter('inn', $filters['inn']);
        }

        if (! empty($filters['phoneNumber'])) {
            $qb->andWhere('le.phoneNumber LIKE :phoneNumber')
                ->setParameter('phoneNumber', '%'.$filters['phoneNumber'].'%');
        }

        if (! empty($filters['email'])) {
            $qb->andWhere('le.email LIKE :email')
                ->setParameter('email', '%'.$filters['email'].'%');
        }

        if (! empty($filters['curatorUid'])) {
            $qb->andWhere('le.curatorUid = :curatorUid')
                ->setParameter('curatorUid', $filters['curatorUid']);
        }

        return $qb->getQuery()->getResult();
    }

    public function save(LegalEntity $legalEntity): LegalEntity
    {
        $this->em->persist($legalEntity);
        $this->em->flush();

        return $legalEntity;
    }

    public function delete(Id $uid): bool
    {
        $legalEntity = $this->findByUid($uid);
        if (! $legalEntity) {
            return false;
        }

        $this->em->remove($legalEntity);
        $this->em->flush();

        return true;
    }

    public function existsByInn(string $inn): bool
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('COUNT(le.uid)')
            ->from(LegalEntity::class, 'le')
            ->andWhere('le.taxNumber.inn = :inn')
            ->setParameter('inn', $inn);

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function findByCurator(UserId $curatorUid): array
    {
        return $this->em->getRepository(LegalEntity::class)
            ->findBy(['curatorUid' => $curatorUid->value()]);
    }

    public function findByCreator(UserId $creatorUid): array
    {
        return $this->em->getRepository(LegalEntity::class)
            ->findBy(['creatorUid' => $creatorUid->value()]);
    }
}
