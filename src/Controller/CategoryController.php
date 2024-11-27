<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends AbstractController
{
    #[Route('/api/categories', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $category = $serializer->deserialize($request->getContent(), Category::class, 'json');
        $entityManager->persist($category);
        $entityManager->flush();
        
        return $this->json($category, Response::HTTP_CREATED);
    }

    #[Route('/api/categories', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $categories = $entityManager->getRepository(Category::class)->findAll();
        $json = $serializer->serialize($categories, 'json');
        
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/api/categories/{id}', methods: ['GET'])]
    public function show(Category $category, SerializerInterface $serializer): JsonResponse
    {
        $json = $serializer->serialize($category, 'json');
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/api/categories/{id}', methods: ['PUT'])]
    public function update(Request $request, Category $category, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $serializer->deserialize($request->getContent(), Category::class, 'json', ['object_to_populate' => $category]);
        $entityManager->flush();
        
        return $this->json($category);
    }

    #[Route('/api/categories/{id}', methods: ['DELETE'])]
    public function delete(Category $category, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($category);
        $entityManager->flush();
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
