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

    #[Route('/{id}', name: 'app_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        $logger->info('Delete method called for category ID: ' . $category->getId());
    
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'))) {
            $logger->info('CSRF token is valid');
            try {
                // Gérer les Reminders associés
                foreach ($category->getIdReminder() as $reminder) {
                    $reminder->setIdCategory(null);
                    $entityManager->persist($reminder);
                }
    
                $logger->info('Attempting to remove category');
                $entityManager->remove($category);
                $logger->info('Category marked for removal');
                
                $logger->info('Flushing changes to database');
                $entityManager->flush();
                $logger->info('Changes flushed successfully');
    
                $this->addFlash('success', 'Category deleted successfully.');
                $logger->info('Success flash message added');
            } catch (\Exception $e) {
                $logger->error('Error during category deletion: ' . $e->getMessage());
                $this->addFlash('error', 'Error deleting category: ' . $e->getMessage());
            }
        } else {
            $logger->warning('Invalid CSRF token');
            $this->addFlash('error', 'Invalid token');
        }
    
        $logger->info('Redirecting to category index');
        return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
