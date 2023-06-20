<?php

namespace App\Controller;

use App\Entity\Food;
use App\Form\FoodAddType;
use App\Repository\FoodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use App\Repository\CategoryRepository;

class FoodController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/food/{page}", name="food", requirements={"page"="\d+"})
     * @return Response
     */
    public function menu(Security $security, Environment $twig, CategoryRepository $categoryRepository, FoodRepository $foodRepository, int $page = 1): Response
    {
        $eater = $security->getUser();
        $paginator = $foodRepository->findByEater($eater, $page);
        $categories = $categoryRepository->findBy(
            ['eater' => $eater],
            ['name' => 'ASC']
        );
        $maxPages = ceil($paginator->count() / $foodRepository::PAGE_SIZE);

        return new Response($twig->render('food/menu.html.twig', [
            'paginator' => $paginator,
            'maxPages' => $maxPages,
            'currentPage' => $page,
        ]));
    }

    /**
     * @Route("/food/add", name="add_food")
     * @return Response
     */
    public function add(Security $security, Request $request, FoodRepository $foodRepository): Response
    {
        $food = new Food();

        $form = $this->createForm(FoodAddType::class, $food, ['eater' => $security->getUser()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $food = $form->getData();

            if ($food->getAmountType() == Food::AMOUNT_TYPE_GRAM) {
                $food->setWeight(Food::AMOUNT_TYPE_GRAM);
            }
            $food->setEater($security->getUser());

            $foodRepository->add($food);

            $this->entityManager->persist($food);
            $this->entityManager->flush();

            return $this->redirectToRoute('food');
        }

        return new Response($this->renderForm('food/add.html.twig', [
            'foodForm' => $form,
        ]));
    }

    /**
     * Displays a form to update an existing Food entity.
     * @Route("/food/edit/{id}", name="edit_food")
     * @return Response
     */
    public function edit(int $id, Security $security, Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $food = $entityManager->getRepository(Food::class)->find($id);

        if (!$food) {
            throw $this->createNotFoundException(
                'No such product ID: '. $id
            );
        }
        $form = $this->createForm(FoodAddType::class, $food, ['eater' => $security->getUser()]);;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $food = $form->getData();
            $entityManager->flush();

            $this->addFlash(
                'success',
                'The item has been edited'
            );

            return $this->redirectToRoute('food');
        }

        return $this->renderForm('food/update.html.twig', [
            'foodForm' => $form,
            'food' => $food,
        ]);
    }

    /**
     * Deletes a Food entity.
     * @Route("/food/remove/{id}", name="delete_food", methods={"POST"})
     */
    public function remove(Request $request, Food $food, ManagerRegistry $doctrine): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('food');
        }

        $entityManager = $doctrine->getManager();
        $entityManager->remove($food);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'The item has been removed from the menu!'
        );

        return $this->redirectToRoute('food');
    }
}