<?php

namespace App\Controller;

use App\Repository\ReminderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ReminderRepository $reminderRepository): Response
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0); // Réinitialise l'heure à minuit

        $tomorrow = new \DateTime();
        $tomorrow->setTime(23, 59, 59); // Fin de la journée

        $reminders = $reminderRepository->createQueryBuilder('r')
            ->where('r.limitDate BETWEEN :today AND :tomorrow')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->getQuery()
            ->getResult();

        return $this->render('reminder/today.html.twig', [
            'reminders' => $reminders,
        ]);
    }
}
