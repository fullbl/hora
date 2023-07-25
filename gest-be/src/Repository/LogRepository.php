<?php

namespace App\Repository;

use App\Entity\Log;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Log>
 *
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository
{
    private array $logs = [];

    public function __construct(
        ManagerRegistry $registry,         
        private Security $security,
    )
    {
        parent::__construct($registry, Log::class);
    }

    public function prepareForEntity(mixed $entity): void
    {
        $this->getEntityManager()->getUnitOfWork()->computeChangeSets();
        $log = new Log();
        $log
            ->setEntityClass(get_class($entity))
            ->setChanges($this->getEntityManager()->getUnitOfWork()->getEntityChangeSet($entity))
            ->setUser($this->security->getUser())
            ->setCreatedAt(new DateTimeImmutable());
        
        array_push($this->logs, $log);
    }

    public function saveForEntity(mixed $entity): void
    {
        $log = array_pop($this->logs);
        if(null === $log){
            return;
        }
        $log->setEntityId($entity->getId());
        
        $this->getEntityManager()->persist($log);
        $this->getEntityManager()->flush();
    }
    public function save(Log $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Log $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Log[] Returns an array of Log objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Log
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
