<?php

namespace App\Controller;

use App\Entity\Category;
use Psr\Log\LoggerInterface;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CategoryRepository;

#[Route('/category')]
final class CategoryController extends AbstractController
{
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

    #[Route('/{id}', name: 'app_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        // Journaliser l'entrée dans la méthode de suppression
        $logger->info('Entering delete method for category ID: ' . $category->getId());

        // Vérifier la validité du token CSRF
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            try {
                // Déassocier les rappels de la catégorie avant la suppression
                foreach ($category->getIdReminder() as $reminder) {
                    // Défaire l'association ou supprimer les rappels si nécessaire
                    // Pour défaire l'association :
                    // $reminder->setIdCategory(null);
                    // Ou pour supprimer :
                    // $entityManager->remove($reminder);
                }

                // Supprimer la catégorie
                $entityManager->remove($category);
                $entityManager->flush();

                // Ajouter un message flash de succès
                $this->addFlash('success', 'La catégorie a été supprimée avec succès.');
                $logger->info('Category deleted successfully: ID ' . $category->getId());
            } catch (\Exception $e) {
                // Gérer les erreurs lors de la suppression
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression de la catégorie : ' . $e->getMessage());
                $logger->error('Error during category deletion: ' . $e->getMessage());
            }
        } else {
            // Gérer le cas d'un token CSRF invalide
            $this->addFlash('error', 'Token CSRF invalide.');
            $logger->warning('Invalid CSRF token for category ID: ' . $category->getId());
        }

        // Rediriger vers l'index des catégories
        return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
    }
}