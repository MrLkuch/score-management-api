<?php

namespace App\Controller;

use App\Entity\Score;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/scores')]
class ScoreController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $scores = $entityManager->getRepository(Score::class)->findAll();
        return $this->json($scores);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $score = $serializer->deserialize($request->getContent(), Score::class, 'json');
        $entityManager->persist($score);
        $entityManager->flush();
        return $this->json($score, 201);
    }

    #[Route('/api/scores', name: 'get_scores', methods: ['GET'])]
    public function getScores(ScoreRepository $scoreRepository): JsonResponse
    {
        $scores = $scoreRepository->findAll();
        return $this->json($scores, 200, [], ['groups' => 'score:read']);
    }
    // Ajoutez les méthodes pour GET (single), PUT, DELETE
}