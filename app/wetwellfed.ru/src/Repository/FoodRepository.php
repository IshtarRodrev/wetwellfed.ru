<?php

namespace App\Repository;

use App\Entity\Food;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use App\Entity\Eater;

/**
 * @extends ServiceEntityRepository<Food>
 *
 * @method Food|null find($id, $lockMode = null, $lockVersion = null)
 * @method Food|null findOneBy(array $criteria, array $orderBy = null)
 * @method Food[]    findAll()
 * @method Food[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FoodRepository extends ServiceEntityRepository
{
    public const PAGE_SIZE = 5;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Food::class);
    }

    /**
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findByEater(Eater $eater = null, $current_page = 1)
    {
        $paginator = new Paginator(
            $this->createQueryBuilder('f')
                ->where('f.eater = :eater')
                ->setParameter('eater', $eater)
                ->orderBy('f.name', 'ASC')
                ->getQuery()
        );

        $paginator->getQuery()
            ->setFirstResult(self::PAGE_SIZE * ($current_page - 1))
            ->setMaxResults(self::PAGE_SIZE);

        return $paginator;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Food $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Food $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->_em->flush();
            // SAME AS
            //$this->getEntityManager()->flush();
        }
    }
}