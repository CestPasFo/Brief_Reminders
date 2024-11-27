<?php

namespace App\Controller;

use App\Entity\Reminder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class ReminderController extends AbstractController
{
    #[Route('/api/reminders', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $reminder = $serializer->deserialize($request->getContent(), Reminder::class, 'json');
        $entityManager->persist($reminder);
        $entityManager->flush();
        
        return $this->json($reminder, Response::HTTP_CREATED);
    }

    #[Route('/api/reminders', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $reminders = $entityManager->getRepository(Reminder::class)->findAll();
        $json = $serializer->serialize($reminders, 'json');
        
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/api/reminders/{id}', methods: ['GET'])]
    public function show(Reminder $reminder, SerializerInterface $serializer): JsonResponse
    {
        $json = $serializer->serialize($reminder, 'json');
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/api/reminders/{id}', methods: ['PUT'])]
    public function update(Request $request, Reminder $reminder, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $serializer->deserialize($request->getContent(), Reminder::class, 'json', ['object_to_populate' => $reminder]);
        $entityManager->flush();
        
        return $this->json($reminder);
    }

    #[Route('/api/reminders/{id}', methods: ['DELETE'])]
    public function delete(Reminder $reminder, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($reminder);
        $entityManager->flush();
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
