<?php

namespace App\Controller;

use App\Entity\Category; //?
use App\Entity\Eater;
use App\Entity\Food; //?
use App\Form\CategoryAddType;
//use App\Message\MealMessage;
use App\Repository\FoodRepository;
use Doctrine\ORM\EntityManagerInterface;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Doctrine\Persistence\ManagerRegistry;

//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use App\Repository\CategoryRepository;

class CategoryController extends AbstractController
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
     * @Route("/category/tree", name="category_tree")
     * @return Response
     */
    public function tree(Security $security, Environment $twig, CategoryRepository $categoryRepository, int $page = 1): Response
    {
        $eater = $security->getUser();

        $roots = $categoryRepository->getRootCategory($security->getUser());

        return new Response($twig->render('category/tree.html.twig', [
            'roots' => $roots,
        ]));
    }

    /**
     * @Route("/category/{page}", name="category", requirements={"page"="\d+"})
     * @return Response
     */
    public function index(Security $security, Environment $twig, CategoryRepository $categoryRepository, int $page = 1): Response
    {
        $eater = $security->getUser();

        $paginator = $categoryRepository->getAllCategory($eater, $page);
        $maxPages = ceil($paginator->count() / $categoryRepository::PAGE_SIZE);

        return new Response($twig->render('category/index.html.twig', [
            'paginator' => $paginator,
            'maxPages' => $maxPages,
            'currentPage' => $page,
        ]));
    }

    /**
     * @Route("/category/add", name="add_category")
     * @return Response
     */
    public function add(Security $security, Request $request, CategoryRepository $categoryRepository): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryAddType::class, $category, ['eater' => $security->getUser(), 'exclude' => null]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $category->setEater($security->getUser());

            $categoryRepository->add($category);

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            return $this->redirectToRoute('category');
        }

        return new Response($this->renderForm('category/add.html.twig', [
            'categoryForm' => $form,
        ]));
    }

    /**
     * Displays a form to update an existing Category entity.
     * @Route("/category/edit/{id}", name="edit_category")
     * @return Response
     */
    public function edit(int $id, Security $security, Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $category = $entityManager->getRepository(Category::class)->find($id);

        $form = $this->createForm(CategoryAddType::class, $category, ['eater' => $security->getUser(), 'exclude' => $id]);;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $entityManager->flush();

            $this->addFlash(
                'success',
                'The item has been edited'
            );

            return $this->redirectToRoute('category');
        }

        return $this->renderForm('category/update.html.twig', [
            'categoryForm' => $form,
            'category' => $category,
        ]);
    }

    /**
     * Deletes a Category entity.
     * @Route("/category/remove/{id}", name="delete_category", methods={"POST"})
     */
    public function remove(Request $request, Category $food, ManagerRegistry $doctrine): Response
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