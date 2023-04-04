<?php

namespace App\Repository;

use App\Entity\Meal;
use App\Entity\Eater;
use Cassandra\Date;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * @extends ServiceEntityRepository<Meal>
 *
 * @method Meal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Meal|null findOneBy(array $criteria, array $orderBy = null)
// * @method Meal[]    findAll()
 * @method Meal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MealRepository extends ServiceEntityRepository
{
    public const PAGE_SIZE = 3;
//    public const PAGE_SIZE = 1;

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
//                ->innerJoin('m.eater', 'e')
                ->orderBy('m.eatenAt', 'DESC')
                ->getQuery()
        );

        $paginator->getQuery()
            ->setFirstResult(self::PAGE_SIZE * ($current_page - 1))
            ->setMaxResults(self::PAGE_SIZE);

        return $paginator;
    }

    public function getHistory(Eater $eater)
    {
        $timezone = new \DateTimeZone("Europe/Moscow");
        $now = new \DateTime();
        $before = new \DateTime('-7 days');

        $now->setTimezone($timezone);
        $before->setTimezone($timezone);

        $timeline = [];
        $i = 0;
        do {
            $time = strtotime( '-'. $i .' days', time());
            $date = \date('Y-m-d', $time);
            $i++;
            $timeline[$date] = ['date' => $date, 'kcal' => 0];
        }
        while ($i < 7);
        /*
         * $timeline должен быть в таком стиле:
         * [
         *      "2023-03-26" => [ "date" => "2023-03-26", "calories" => 2500],
         *      ...
         * ]
         *
         * но это всё в идеале и в конце.
         * а конкретно в цикле сверху должен получиться такой массив:
         * [
         *      "Y-m-d" => [ "date" => "Y-m-d", "calories" => 0], // заметь: что именно ноль
         *      // и тут остальные 6 дней
         *      // ключ даты нужен для того, чтобы ты ниже смогла обратиться напрямую к нужому "дню" в массиве
         * ]
         */

//        echo "<pre>";
//        var_dump($timeline);
//        echo "</pre>";
//        die;
        $rows = $this->createQueryBuilder('m')
            ->select('m.eatenAt', 'm.calories')
            ->where('m.eater = :eater')
            ->setParameter('eater', $eater)
            ->andWhere('m.eatenAt > :begin')
            ->setParameter('begin', $before->format("Y-m-d H:i:s"))
            ->andWhere('m.eatenAt < :end')
            ->setParameter('end', $now->format("Y-m-d H:i:s"))
////                ->innerJoin('m.eater', 'e')
            ->orderBy('m.eatenAt', 'DESC')
            ->getQuery()
//            ->getSQL()
            ->getResult() // GIVES EMPTY ARRAY!??
        ;

//        var_dump($rows);
//        die();

        $day = [];
        foreach ($rows as $row)
        {
            $date = $row["eatenAt"]->format('Y-m-d');
            $kcal = $row["calories"] ?? 0;

            // только если в массиве таймлайн есть елемент с ключём $date
            if (isset($timeline[$date]) && $timeline[$date]['date'] == $row["eatenAt"]->format('Y-m-d')){
            //      сложить коллории хранящиеся в этом елементе массива
                $timeline[$date]['kcal'] += $kcal;
            }
                //
                // НЕ ДОБАВЛЯЙ НОВЫЕ ЕЛЕМЕНТЫ В МАССИВ
//                echo $row['calories'] . " ";

//            $line = array('date'=>$row["eatenAt"]->format('Y-m-d'), 'kcal' => $kcal);
        }
//        var_dump($timeline);
//        die;

        // TODO: what we need to get:
//          array[
//              0 => [
//                  'date' => "2023-03-18",
//                  'kcal' => 800,
//              ],
//              1 => [
//                  'date' => "2023-03-17",
//                  'kcal' => 0,
//              ],
//              2 => [
//                  'date' => "2023-03-16",
//                  'kcal' => 500,
//              ],
//          ]

        return $timeline;

//        $paginator =
//            new Paginator(
//            $timeline
//        )
//        ;
//
//        $paginator
//            ->setFirstResult(self::PAGE_SIZE * ($current_page - 1))
//            ->setMaxResults(self::PAGE_SIZE);
//
//        return $paginator;
    }

    public function getYearTrack(Eater $eater)
    {
        $todayNum = date("w", time());
        if ($todayNum == 0)
            $todayNum = 7;
        $todayNum -= 1; // это мы делаем, потому что индексы сместились и теперь пн был = 1

        // общее кол-во дней, которые ты исопльзуешь для построения таблички). т.е. если сегодня понедельник, то 365 + 1
        $needWeek = 52;
        $allDays = $needWeek * 7 + $todayNum;

        $before = new \DateTime("-$allDays days");

        $timeline = [
            [], [], [], [], [], [], [],
        ];
        $months = [
//            [ 'name' => "Jan", "weeksInMonth" => 0],
        ];
        //  TODO:
        //   $result['months'] = [
        //       // № of month => amount of weeks
        //       [ 'name' => "Jan", "weeksInMonth" => 5],
        //       [ 'name' => "Feb", "weeksInMonth" => 5],
        //       [ 'name' => "Mar", "weeksInMonth" => 5],
        //       [ 'name' => "Apr", "weeksInMonth" => 5],
        //       [ 'name' => "May", "weeksInMonth" => 5],
        //       [ 'name' => "Jun", "weeksInMonth" => 4],
        //       [ 'name' => "Jul", "weeksInMonth" => 4],
        //       [ 'name' => "Aug", "weeksInMonth" => 4],
        //       [ 'name' => "Sep", "weeksInMonth" => 4],
        //       [ 'name' => "Oct", "weeksInMonth" => 4],
        //       [ 'name' => "Nov", "weeksInMonth" => 4],
        //       [ 'name' => "Dec", "weeksInMonth" => 4],
        //   ];

        $lastMonth = \date('M', strtotime( "-$allDays days", time()));

        for ($weeksInMonth = 0, $i = $allDays; $i >=0; $i--)
        {
            $time = strtotime( '-'. $i .' days', time());
            $date = \date('Y-m-d', $time);
            $idxWeekday = (($todayNum - ($i % 7)) + 7) % 7;
            //шагаем по неделям, сбрасываем счётчик в начале нового месяца

            if (\date('d', $time) == 1) {
                if ($weeksInMonth > 0) {
                    $arr = ['name' => $lastMonth, 'weeksInMonth' => $weeksInMonth];
                    $months[] = $arr;
                }
                $lastMonth = \date('M', $time);

                $weeksInMonth = 0;
            }
            //складываем количество недель в каждый месяц
            // текущий месяц???
            if ((\date('w', $time) == 1)) {
                $weeksInMonth++;
            }

            $cssClass = 'border-class ';

            $nextDayTime = strtotime( '-'. ($i - 1) .' days', time());
            $nextWeekTime = strtotime( '-'. ($i - 7) .' days', time());
            $currentMoneth = \date('m', $time);
            $nextWeekMoneth = \date('m', $nextWeekTime);
            if ( $currentMoneth != $nextWeekMoneth ) {
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
//                ->innerJoin('m.eater', 'e')
            ->orderBy('m.eatenAt', 'DESC')
            ->getQuery()
//            ->getSQL()
            ->getResult() // GIVES EMPTY ARRAY!??
        ;

        foreach ($rows as $row)
        {
            $date = $row["eatenAt"]->format('Y-m-d');
            $kcal = $row["calories"];

            $day = $row["eatenAt"]->format('w');
            if ($day == 0)
                $day = 7;
            $day -= 1; // это мы делаем, потому что индексы сместились и теперь пн был = 1

            // только если в массиве таймлайн есть елемент с ключём $date
            if (isset($timeline[$day][$date]) && $timeline[$day][$date]['date'] == $row["eatenAt"]->format('Y-m-d')){
                //      сложить калории хранящиеся в этом елементе массива
                $timeline[$day][$date]['kcal'] += $kcal;

                $percentage = $timeline[$day][$date]['kcal'] / $eater->getKcalDayNorm() * 100;

                // задаём уровни (почему бы не передавать проценты?)
                $lvl = 1;
                if ($percentage > 70){
                    $lvl +=1;
                }
                if ($percentage > 90){
                    $lvl +=1;
                }
                if ($percentage > 110){
                    $lvl +=1;
                }
                if ($percentage > 130){
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
        $todayStart = "2023-03-17 00:00:00";
        $todayFinish = "2023-03-17 23:59:59";
//        return
        $scalarResult =
        $this->createQueryBuilder('m')
            ->select('SUM (m.calories)')
//            ->from('meal', 'm')
            ->where('m.eater = :eater')
            ->setParameter('eater', $eater)
            ->andWhere('m.eatenAt > :todayS')
            ->setParameter('todayS', $todayStart)
            ->andWhere('m.eatenAt < :todayF')
            ->setParameter('todayF', $todayFinish)
            //                ->innerJoin('m.eater', 'e')
            ->orderBy('m.eatenAt', 'DESC')
            ->getQuery()
            ->getSingleScalarResult()
//            ->getSQL()
        ;
//        var_dump($scalarResult);
//        exit();
        return $scalarResult;
    }
}