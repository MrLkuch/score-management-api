<?php

namespace App\Controller;

use App\Entity\Score;
use App\Entity\Team;
use App\Repository\ScoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/scores')]
class ScoreController extends AbstractController
{
    #[Route('', name: 'get_scores', methods: ['GET'])]
    public function index(ScoreRepository $scoreRepository): JsonResponse
    {
        $scores = $scoreRepository->findAll();
        return $this->json($scores, Response::HTTP_OK, [], ['groups' => 'score:read']);
    }

    #[Route('/{id}', name: 'get_score', methods: ['GET'])]
    public function show(Score $score): JsonResponse
    {
        return $this->json($score, Response::HTTP_OK, [], ['groups' => 'score:read']);
    }

    #[Route('/add', name: 'create_score', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérifiez si un ID d'équipe est fourni
        if (!isset($data['team']['id'])) {
            return $this->json(['error' => 'Team ID is required'], Response::HTTP_BAD_REQUEST);
        }

        // Recherchez l'équipe existante
        $team = $em->getRepository(Team::class)->find($data['team']['id']);
        if (!$team) {
            return $this->json(['error' => 'Team not found'], Response::HTTP_NOT_FOUND);
        }

        // Créez le score
        $score = new Score();
        $score->setPoints($data['points']);
        $score->setTeam($team);

        $errors = $validator->validate($score);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($score);
        $em->flush();

        return $this->json($score, Response::HTTP_CREATED, [], ['groups' => 'score:read']);
    }

    #[Route('/{id}', name: 'update_score', methods: ['PUT'])]
    public function update(Request $request, Score $score, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        $serializer->deserialize($request->getContent(), Score::class, 'json', ['object_to_populate' => $score]);
        
        $errors = $validator->validate($score);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->flush();

        return $this->json($score, Response::HTTP_OK, [], ['groups' => 'score:read']);
    }

    #[Route('/{id}', name: 'delete_score', methods: ['DELETE'])]
    public function delete(Score $score, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($score);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}