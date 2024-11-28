<?php

namespace App\Controller;

use App\Entity\Category;
use Psr\Log\LoggerInterface;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Logger\ConsoleLogger;

#[Route('/category')]
final class CategoryController extends AbstractController{
    #[Route(name: 'app_category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/category/{id}', name: 'app_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        $logger->info('Méthode delete appelée pour la catégorie : ' . $category->getId());

        // Vérification du token CSRF
        $isValid = $this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'));
        $logger->info('Token CSRF valide : ' . ($isValid ? 'Oui' : 'Non'));

        if ($isValid) {
            try {
                $logger->info('Début de la transaction');
                $entityManager->beginTransaction();

                $logger->info('Suppression de la catégorie');
                $entityManager->remove($category);
                
                $logger->info('Flush des changements');
                $entityManager->flush();
                
                $logger->info('Commit de la transaction');
                $entityManager->commit();

                $this->addFlash('success', 'Catégorie supprimée avec succès.');
                $logger->info('Catégorie supprimée avec succès');
            } catch (\Exception $e) {
                $entityManager->rollback();
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
                $logger->error('Erreur lors de la suppression : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide');
            $logger->warning('Tentative de suppression avec un token CSRF invalide');
        }
        return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
