<?php

namespace App\Repository;

use App\Entity\Delivery;
use App\Entity\User;
use App\Entity\Zone;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
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

    public function findAllFiltered(Collection $zones): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.customer', 'c')
            ->andWhere(':zones MEMBER OF c.zones')
            ->setParameter('zones', $zones)
            ->getQuery()
            ->getResult();
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
        $entity->setDeletedAt(new \DateTimeImmutable());

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Delivery[]
     */
    public function findNext(DateTimeImmutable $from, int $weeks, ?Collection $zones = null): array
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Delivery::class, 'd');
        $rsm->addScalarResult('max_delivery', 'max_delivery', Types::DATE_IMMUTABLE);

        $join = ' LEFT JOIN (
            SELECT
                dd.id,
                MAX(dd.delivery_date) OVER (PARTITION BY dd.customer_id) AS max_delivery
            FROM
                delivery dd
        ) rd ON d.id = rd.id';

        $where = ' d.deleted_at IS NULL AND d.delivery_date BETWEEN :from AND :to';

        $parameters = [
            'from' => $from->format('Y-m-d'),
            'to' => $from->modify('+' . $weeks . ' week')->format('Y-m-d'),
        ];


        if (null !== $zones) {
            $join .= ' LEFT JOIN user_zone uz ON d.customer_id = uz.user_id';
            $where .= ' AND uz.zone_id IN (:zones) ';
            $parameters['zones'] = $zones->map(fn (Zone $z) => $z->getId())->toArray();
        }

        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT
            d.*,
            rd.max_delivery
        FROM
            delivery d'
                . $join .
                ' WHERE' . $where,
            $rsm
        );

        $query->setParameters($parameters);

        return array_map(
            /**
             * @param array<array{0: Delivery, 'max_delivery': string}> $row
             */
            function (array $row): Delivery {
                $row[0]->setLastWarning(false);
                $row[0]->setWarning(false);
                if ($row['max_delivery'] <= $row[0]->getDeliveryDate()) {
                    $row[0]->setLastWarning(true);
                    return $row[0];
                }
                if ($row['max_delivery']->modify('-1 month') <= $row[0]->getDeliveryDate()) {
                    $row[0]->setWarning(true);
                }

                return $row[0];
            },
            $query->getResult()
        );
    }

    public function findFreeDeliveryInSameWeek(Delivery $delivery): Delivery
    {
        $freeDelivery = $this->createQueryBuilder('d')
            ->where('d.deletedAt IS NULL')
            ->andWhere('d.deliveryDate BETWEEN :from AND :to')
            ->setParameter('from', $delivery->getDeliveryDate()->modify('monday 00:00:00')->format('Y-m-d'))
            ->setParameter('to', $delivery->getDeliveryDate()->modify('sunday 23:59:59')->format('Y-m-d'))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $freeDelivery) {
            $freeDelivery = clone $delivery;
            $freeDelivery->setCustomer(null);
            $this->getEntityManager()->persist($delivery);
        }

        return $freeDelivery;
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
