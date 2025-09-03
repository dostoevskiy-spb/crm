<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Infrastructure\Persistence\Doctrine\Repository;

use App\Modules\Equipment\Domain\Contracts\EquipmentRepositoryInterface;
use App\Modules\Equipment\Domain\Models\Equipment;
use App\Modules\Equipment\Domain\ValueObjects\Id;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

final class DoctrineEquipmentRepository implements EquipmentRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function findByUid(Id $uid): ?Equipment
    {
        return $this->em->find(Equipment::class, $uid->value());
    }

    public function findAll(): ArrayCollection
    {
        return new ArrayCollection($this->em->getRepository(Equipment::class)->findAll());
    }

    public function findByFilters(array $filters): ArrayCollection
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('e')->from(Equipment::class, 'e');

        if (! empty($filters['uid'])) {
            $qb->andWhere('e.uid = :uid')
                ->setParameter('uid', $filters['uid']);
        }
        if (! empty($filters['name'])) {
            $qb->andWhere('e.name.value LIKE :name')
                ->setParameter('name', '%'.$filters['name'].'%');
        }
        if (! empty($filters['status'])) {
            $qb->andWhere('e.status = :status')
                ->setParameter('status', $filters['status']);
        }
        if (! empty($filters['transportUid'])) {
            $qb->andWhere('e.transportUid = :transportUid')
                ->setParameter('transportUid', $filters['transportUid']);
        }
        if (! empty($filters['warehouse'])) {
            $qb->andWhere('e.warehouse = :warehouse')
                ->setParameter('warehouse', $filters['warehouse']);
        }
        if (! empty($filters['issuedToUid'])) {
            $qb->andWhere('e.issuedToUid = :issuedToUid')
                ->setParameter('issuedToUid', $filters['issuedToUid']);
        }

        return $qb->getQuery()->getResult();
    }

    public function save(Equipment $equipment): Equipment
    {
        $this->em->persist($equipment);
        $this->em->flush();

        return $equipment;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
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
}
