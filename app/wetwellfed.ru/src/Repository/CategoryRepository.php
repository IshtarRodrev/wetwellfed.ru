<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Eater;
use App\Entity\Food;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * @extends ServiceEntityRepository<Food>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public const PAGE_SIZE = 10000000;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getRootCategory(Eater $eater = null)
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.parent IS NULL')
            ->andWhere('c.eater = :eater')
            ->setParameter('eater', $eater)
            ->orderBy('c.name', 'ASC')
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getAllCategory(Eater $eater = null, $current_page = 1)
    {
        $paginator = new Paginator(
            $this->createQueryBuilder('c')
                ->where('c.eater = :eater')
                ->setParameter('eater', $eater)
                ->orderBy('c.name', 'ASC')
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
    public function add(Category $entity, bool $flush = false): void
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