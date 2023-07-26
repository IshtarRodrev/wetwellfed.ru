<?php

namespace App\Controller;

use App\Entity\Meal;
use App\Entity\Food;
use App\Form\MealAddType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MealRepository;
use App\Repository\FoodRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class MealController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/meal/{page}", name="meal", requirements={"page"="\d+"})
     * @return Response
     */
    public function index(Security $security, Environment $twig, MealRepository $mealRepository, int $page = 1): Response
    {
        $eater = $security->getUser();
        $paginator = $mealRepository->findByEater($eater, $page);
        $maxPages = ceil($paginator->count() / $mealRepository::PAGE_SIZE);

        return new Response($twig->render('meal/index.html.twig', [
            'foodList' => $mealRepository->findByEater(),
            'paginator' => $paginator,
            'maxPages' => $maxPages,
            'currentPage' => $page,
        ]));
    }

    /**
     * @Route("/meal/add", name="add_meal")
     * @return Response
     */
    public function add(Security $security, Request $request, MealRepository $mealRepository, FoodRepository $foodRepository): Response
    {
        $meal = new Meal();
        $form = $this->createForm(MealAddType::class, $meal, ['eater' => $security->getUser()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $meal = $form->getData();

            $meal->setEater($security->getUser());

            $mealRepository->add($meal);

            $this->entityManager->persist($meal);
            $this->entityManager->flush();

            return $this->redirectToRoute('meal');
        }

        return new Response($this->renderForm('meal/add.html.twig', [
            'mealForm' => $form,
        ]));
    }

    /**
     * @Route("/meal/edit/{id}", name="edit_meal")
     * @return Response
     */
    public function edit(int $id, Security $security, Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $meal = $entityManager->getRepository(Meal::class)->find($id);

        if (!$meal) {
            throw $this->createNotFoundException(
                'No such product ID: '. $id
            );
        }
        $form = $this->createForm(MealAddType::class, $meal, ['eater' => $security->getUser()]);;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $meal = $form->getData();
            $entityManager->flush();

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