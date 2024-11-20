<?php

namespace App\Controller;

use App\Entity\Team;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/teams')]
class TeamController extends AbstractController
{
    #[Route('', name: 'get_teams', methods: ['GET'])]
    public function index(TeamRepository $teamRepository): JsonResponse
    {
        $teams = $teamRepository->findAll();
        return $this->json($teams, Response::HTTP_OK, [], ['groups' => 'team:read']);
    }

    #[Route('/{id}', name: 'get_team', methods: ['GET'])]
    public function show(Team $team): JsonResponse
    {
        return $this->json($team, Response::HTTP_OK, [], ['groups' => 'team:read']);
    }

    #[Route('', name: 'create_team', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        $team = $serializer->deserialize($request->getContent(), Team::class, 'json');
        
        $errors = $validator->validate($team);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($team);
        $em->flush();

        return $this->json($team, Response::HTTP_CREATED, [], ['groups' => 'team:read']);
    }

    #[Route('/{id}', name: 'update_team', methods: ['PUT'])]
    public function update(Request $request, Team $team, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        $serializer->deserialize($request->getContent(), Team::class, 'json', ['object_to_populate' => $team]);
        
        $errors = $validator->validate($team);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->flush();

        return $this->json($team, Response::HTTP_OK, [], ['groups' => 'team:read']);
    }

    #[Route('/{id}', name: 'delete_team', methods: ['DELETE'])]
    public function delete(Team $team, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($team);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}