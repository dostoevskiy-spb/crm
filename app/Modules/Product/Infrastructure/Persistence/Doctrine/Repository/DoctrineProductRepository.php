<?php

declare(strict_types=1);

namespace App\Modules\Product\Infrastructure\Persistence\Doctrine\Repository;

use App\Modules\Product\Domain\Contracts\ProductRepositoryInterface;
use App\Modules\Product\Domain\Models\Product;
use App\Modules\Product\Domain\ValueObjects\Id;
use App\Modules\Product\Domain\ValueObjects\Sku;
use App\Modules\User\Domain\ValueObjects\Id as UserId;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineProductRepository implements ProductRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findByUid(Id $uid): ?Product
    {
        return $this->em->find(Product::class, $uid->value());
    }

    public function findBySku(Sku $sku): ?Product
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p')
            ->andWhere('p.skuValue = :sku')
            ->setParameter('sku', $sku->value())
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByCode1c(string $code1c): ?Product
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from(Product::class, 'p')
            ->andWhere('p.code1c = :code')
            ->setParameter('code', $code1c)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAll(): array
    {
        return $this->em->getRepository(Product::class)->findAll();
    }

    public function findByFilters(array $filters): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p')->from(Product::class, 'p');

        if (isset($filters['status'])) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', (string) $filters['status']);
        }
        if (isset($filters['type'])) {
            $qb->andWhere('p.type = :type')
                ->setParameter('type', (string) $filters['type']);
        }
        if (isset($filters['unit'])) {
            $qb->andWhere('p.unit.value = :unit')
                ->setParameter('unit', (string) $filters['unit']);
        }
        if (isset($filters['group'])) {
            $qb->andWhere('p.groupName = :group')
                ->setParameter('group', (string) $filters['group']);
        }
        if (isset($filters['subgroup'])) {
            $qb->andWhere('p.subgroupName = :subgroup')
                ->setParameter('subgroup', (string) $filters['subgroup']);
        }
        if (isset($filters['code1c'])) {
            $qb->andWhere('p.code1c = :code1c')
                ->setParameter('code1c', (string) $filters['code1c']);
        }
        if (isset($filters['sku'])) {
            $qb->andWhere('p.skuValue = :sku')
                ->setParameter('sku', (string) $filters['sku']);
        }
        if (isset($filters['name'])) {
            $term = '%'.$filters['name'].'%';
            $qb->andWhere('p.name.value LIKE :term')
                ->setParameter('term', $term);
        }

        return $qb->getQuery()->getResult();
    }

    public function save(Product $product): Product
    {
        $this->em->persist($product);
        $this->em->flush();

        return $product;
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

    public function existsBySku(Sku $sku): bool
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('COUNT(p.uid)')
            ->from(Product::class, 'p')
            ->andWhere('p.skuValue = :sku')
            ->setParameter('sku', $sku->value());

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function existsByCode1c(string $code1c): bool
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('COUNT(p.uid)')
            ->from(Product::class, 'p')
            ->andWhere('p.code1c = :code')
            ->setParameter('code', $code1c);

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function findByCreator(UserId $creatorUid): array
    {
        return $this->em->getRepository(Product::class)
            ->findBy(['creatorUid' => $creatorUid->value()]);
    }
}
