<?php

namespace App\Repository;

use App\Entity\Meal;
use App\Entity\Eater;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * @extends ServiceEntityRepository<Meal>
 *
 * @method Meal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Meal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Meal[]    findAll()
 * @method Meal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MealRepository extends ServiceEntityRepository
{
    public const PAGE_SIZE = 5;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meal::class);
    }

    public function findByEater(Eater $eater = null, $current_page = 1)
    {
        $paginator = new Paginator(
            $this->createQueryBuilder('m')
                ->where('m.eater = :eater')
                ->setParameter('eater', $eater)
                ->orderBy('m.eatenAt', 'DESC')
                ->getQuery()
        );

        $paginator->getQuery()
            ->setFirstResult(self::PAGE_SIZE * ($current_page - 1))
            ->setMaxResults(self::PAGE_SIZE);

        return $paginator;
    }

    public function getHistory(Eater $eater, $days = 7)
    {
        $timezone = new \DateTimeZone("Europe/Moscow");
        $now = new \DateTime();
        $before = new \DateTime("-$days days");

        $now->setTimezone($timezone);
        $before->setTimezone($timezone);

        $timeline = [];
        $i = 0;
        do {
            $time = strtotime( '-'. $i .' days', time());
            $date = \date('Y-m-d', $time);
            $i++;
            $timeline[$date] = ['date' => $date, 'kcal' => 0];
        } while ($i < $days);

        $rows = $this->createQueryBuilder('m')
            ->select('m.eatenAt', 'm.calories')
            ->where('m.eater = :eater')
            ->setParameter('eater', $eater)
            ->andWhere('m.eatenAt > :begin')
            ->setParameter('begin', $before->format("Y-m-d H:i:s"))
            ->andWhere('m.eatenAt < :end')
            ->setParameter('end', $now->format("Y-m-d H:i:s"))
            ->orderBy('m.eatenAt', 'DESC')
            ->getQuery()
//            ->getSQL()
            ->getResult() // gives empty array!??
        ;

        foreach ($rows as $row) {
            $date = $row["eatenAt"]->format('Y-m-d');
            $kcal = $row["calories"] ?? 0;

            if (isset($timeline[$date]) && $timeline[$date]['date'] == $row["eatenAt"]->format('Y-m-d')) {
                $timeline[$date]['kcal'] += $kcal;
            }
        }
        return $timeline;
    }

    public function getYearTrack(Eater $eater)
    {
        $todayNum = date("w", time());
        if ($todayNum == 0)
            $todayNum = 7;
        $todayNum -= 1;
        $needWeek = 52;
        $allDays = $needWeek * 7 + $todayNum;

        $timeline = [
            [], [], [], [], [], [], [],
        ];
        $months = [
        ];

        $lastMonth = \date('M', strtotime( "-$allDays days", time()));

        for ($weeksInMonth = 0, $i = $allDays; $i >=0; $i--) {
            $time = strtotime( '-'. $i .' days', time());
            $date = \date('Y-m-d', $time);
            $idxWeekday = (($todayNum - ($i % 7)) + 7) % 7;

            if (\date('d', $time) == 1) {
                if ($weeksInMonth > 0) {
                    $arr = ['name' => $lastMonth, 'weeksInMonth' => $weeksInMonth];
                    $months[] = $arr;
                }
                $lastMonth = \date('M', $time);
                $weeksInMonth = 0;
            }
            if ((\date('w', $time) == 1)) {
                $weeksInMonth++;
            }

            $cssClass = 'border-class ';

            $nextDayTime = strtotime( '-'. ($i - 1) .' days', time());
            $nextWeekTime = strtotime( '-'. ($i - 7) .' days', time());
            $currentMonth = \date('m', $time);
            $nextWeekMonth = \date('m', $nextWeekTime);
            if ($currentMonth != $nextWeekMonth) {
                $cssClass .= "border-right ";
            }
            if (\date('d', $nextDayTime) == 1 && (\date('w', $nextDayTime) != 1)) {;
                $cssClass .= "border-bottom ";
            }

            $timeline[$idxWeekday][$date] = ['date' => $date, 'kcal' => 0, 'lvl' => 0, 'cssClass' => $cssClass];
        }

        $timezone = new \DateTimeZone("Europe/Moscow");
        $now = new \DateTime();
        $before = new \DateTime("-$allDays days");

        $now->setTimezone($timezone);
        $before->setTimezone($timezone);

        $rows = $this->createQueryBuilder('m')
            ->select('m.eatenAt', 'm.calories')
            ->where('m.eater = :eater')
            ->setParameter('eater', $eater)
            ->andWhere('m.eatenAt > :begin')
            ->setParameter('begin', $before->format("Y-m-d H:i:s"))
            ->andWhere('m.eatenAt < :end')
            ->setParameter('end', $now->format("Y-m-d H:i:s"))
            ->orderBy('m.eatenAt', 'DESC')
            ->getQuery()
//            ->getSQL()
            ->getResult() // gives empty array!??
        ;

        foreach ($rows as $row) {
            $date = $row["eatenAt"]->format('Y-m-d');
            $kcal = $row["calories"];

            $day = $row["eatenAt"]->format('w');
            if ($day == 0)
                $day = 7;
            $day -= 1;
            if (isset($timeline[$day][$date]) && $timeline[$day][$date]['date'] == $row["eatenAt"]->format('Y-m-d')) {
                $timeline[$day][$date]['kcal'] += $kcal;

                $percentage = $timeline[$day][$date]['kcal'] / $eater->getKcalDayNorm() * 100;
                $lvl = 1;
                if ($percentage > 70) {
                    $lvl +=1;
                }
                if ($percentage > 90) {
                    $lvl +=1;
                }
                if ($percentage > 110) {
                    $lvl +=1;
                }
                if ($percentage > 130) {
                    $lvl +=1;
                }
                $timeline[$day][$date]['lvl'] = $lvl;
            }
        }
        $result = [
            'timeline' => $timeline,
            'months' => $months,
        ];
        return $result;
    }

    /**
    * @throws ORMException
    * @throws OptimisticLockException
    */
    public function add(Meal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Meal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getCalToday(Eater $eater = null)
    {
        $today = date("Y-m-d");
        $todayStart = $today . " 00:00:00";
        $todayFinish = $today . " 23:59:59";

        $scalarResult = $this->createQueryBuilder('m')
            ->select('SUM (m.calories)')
            ->where('m.eater = :eater')
            ->setParameter('eater', $eater)
            ->andWhere('m.eatenAt > :todayS')
            ->setParameter('todayS', $todayStart)
            ->andWhere('m.eatenAt < :todayF')
            ->setParameter('todayF', $todayFinish)
            ->orderBy('m.eatenAt', 'DESC')
            ->getQuery()
            ->getSingleScalarResult()
//            ->getSQL()
        ;
        return $scalarResult ?? 0;
    }
}