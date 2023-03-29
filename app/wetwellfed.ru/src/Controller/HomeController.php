<?php

namespace App\Controller;

use App\Entity\Meal;
use App\Form\MealAddType;
use App\Repository\EaterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MealRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Security;
use App\Form\HomeMealAddType;

class HomeController extends AbstractController
{
    private $twig;
    private $entityManager;
    private $bus;

    public function __construct(Environment $twig, EntityManagerInterface $entityManager, MessageBusInterface $bus)
    {
        $this->twig = $twig; // избавляемся от дублирования Environment $twig в методах
        $this->entityManager = $entityManager;
        $this->bus = $bus;
    }

    /**
     * @Route("/", name="homepage")
     * @return Response
     */

    // TODO: 1. dateformat()
    //       2. Цветовая таблица зависимости /
    public function index(Security $security, Request $request, Environment $twig, EaterRepository $eaterRepository, MealRepository $mealRepository, ManagerRegistry $doctrine): Response
    {
        if (!$security->getUser()) {
            return $this->redirectToRoute('login');
        }
        $uid = $security->getUser();
        $eater = $eaterRepository->findOneBy([
            'id' => $uid,
        ]);
        $BMR = $eater->getKcalDayNorm();
        $todayScore = $mealRepository->getCalToday($eater);

        $this->entityManager = $doctrine->getManager();
        $meal = new Meal();
        $form = $this->createForm(HomeMealAddType::class, $meal, ['eater' => $uid]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $meal = $form->getData();
            $meal->setEater($security->getUser());
            $mealRepository->add($meal);
            $this->entityManager->persist($meal);
            $this->entityManager->flush();
            return $this->redirectToRoute('homepage');
        }

        return new Response($this->renderForm('home/index.html.twig', [
            'eater' => $eater,
            'maxScore' => $BMR,
            'todayScore' => $todayScore,
            'homeForm' => $form,
        ]));
//        return new Response($this->renderForm('meal/add.html.twig', [
//            'mealForm' => $form,
//        ]));
    }

    /**
     * @Route("/history", name="home_meal_history")
     * @return Response
     */
    public function history(Security $security, Request $request, EaterRepository $eaterRepository, MealRepository $mealRepository, ManagerRegistry $doctrine): Response
    {
        $uid = $security->getUser();
        if (!$uid) {
            return $this->redirectToRoute('app_login');
        }

        $eater = $eaterRepository->findOneBy([
            'id' => $uid,
        ]);

        $timeline = $mealRepository->getHistory($eater);

//        var_dump($timeline);
//        die;

        return new Response($this->render('home/history.html.twig', [
            'timeline' => $timeline,
        ]));
    }

    /**
     * @Route("/history/year", name="home_meal_year")
     * @return Response
     */
    public function yearTrack(Security $security, Request $request, EaterRepository $eaterRepository, MealRepository $mealRepository, ManagerRegistry $doctrine): Response
    {
        $uid = $security->getUser();
        if (!$uid) {
            return $this->redirectToRoute('app_login');
        }

        $eater = $eaterRepository->findOneBy([
            'id' => $uid,
        ]);

        $result = $mealRepository->getYearTrack($eater);

//        var_dump($timeline);
//        die;

        return new Response($this->render('home/year.html.twig', [
            'timeline' => $result['timeline'],
            'months' => $result['months'],
        ]));
    }

    /**
     * @Route("/meal/edit/{id}", name="edit_meal")
     * @return Response
     */
    public function edit(int $id, Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $meal = $entityManager->getRepository(Meal::class)->find($id);

        if (!$meal) {
            throw $this->createNotFoundException(
                'No such product ID: '. $id
            );
        }
        $form = $this->createForm(MealAddType::class, $meal);;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $meal = $form->getData();
            $entityManager->flush();

//            $this->addFlash(
//                'success',
//                'The item has been edited'
//            );

            return $this->redirectToRoute('meal');
        }

        return $this->renderForm('meal/update.html.twig', [
            'mealForm' => $form,
            'meal' => $meal,
        ]);
    }

    /**
     * Deletes a Meal entity.
     * @Route("/meal/remove/{id}", name="delete_meal", methods={"POST"})
     */
    public function remove(int $id, Request $request, Meal $meal, ManagerRegistry $doctrine): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('meal');
        }

        $entityManager = $doctrine->getManager();
        $entityManager->remove($meal);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'The item has been removed from the journal!'
        );

        return $this->redirectToRoute('meal');
    }
}