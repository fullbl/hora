<?php

namespace App\Repository;

use App\Entity\Delivery;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Delivery>
 *
 * @method Delivery|null find($id, $lockMode = null, $lockVersion = null)
 * @method Delivery|null findOneBy(array $criteria, array $orderBy = null)
 * @method Delivery[]    findAll()
 * @method Delivery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Delivery::class);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function save(Delivery $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Delivery $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function intersectingWeeks(array $weeks, ?User $customer, ?int $id): array
    {
        $deliveriesQb = $this->createQueryBuilder('d')
            ->where('d.customer = :customer')
            ->setParameter('customer', $customer);

        if (null !== $id) {
            $deliveriesQb
                ->andWhere('d.id != :id')
                ->setParameter('id', $id);
        }
        $deliveries = $deliveriesQb
            ->getQuery()
            ->getResult();

        $intersecting = [];
        foreach ($deliveries as $delivery) {
            $intersecting = array_merge($intersecting, array_intersect($delivery->getWeeks(), $weeks));
        }

        return $intersecting;
    }
}
