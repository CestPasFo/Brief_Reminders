<?php

namespace App\Controller;

use App\Entity\Reminder;
use App\Form\ReminderType;
use App\Repository\ReminderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reminder')]
final class ReminderController extends AbstractController
{
    #[Route(name: 'app_reminder_index', methods: ['GET'])]
    public function index(ReminderRepository $reminderRepository): Response
    {
        return $this->render('reminder/index.html.twig', [
            'reminders' => $reminderRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reminder_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reminder = new Reminder();
        $form = $this->createForm(ReminderType::class, $reminder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Assurez-vous d'associer une catégorie si nécessaire
            // Exemple : $category = ...; // Récupérer la catégorie sélectionnée
            // $reminder->setIdCategory($category);

            $entityManager->persist($reminder);
            $entityManager->flush();

            $this->addFlash('success', 'Rappel créé avec succès.');

            return $this->redirectToRoute('app_reminder_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reminder/new.html.twig', [
            'reminder' => $reminder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reminder_show', methods: ['GET'])]
    public function show(Reminder $reminder): Response
    {
        return $this->render('reminder/show.html.twig', [
            'reminder' => $reminder,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reminder_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reminder $reminder, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReminderType::class, $reminder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si vous avez besoin de gérer des associations ici, faites-le.
            // Exemple : Associer une nouvelle catégorie si nécessaire

            $entityManager->flush();

            $this->addFlash('success', 'Rappel mis à jour avec succès.');

            return $this->redirectToRoute('app_reminder_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reminder/edit.html.twig', [
            'reminder' => $reminder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_reminder_delete', methods: ['POST'])]
    public function delete(Request $request, Reminder $reminder, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reminder->getId(), $request->request->get('_token'))) {
            try {
                // Supprimer le rappel
                $entityManager->remove($reminder);
                $entityManager->flush();

                // Message flash de succès
                $this->addFlash('success', 'Rappel supprimé avec succès.');
            } catch (\Exception $e) {
                // Gestion des erreurs lors de la suppression
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression du rappel : ' . $e->getMessage());
            }
        } else {
            // Gérer le cas d'un token CSRF invalide
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_reminder_index', [], Response::HTTP_SEE_OTHER);
    }
}