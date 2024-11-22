<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\Team;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/players')]
class PlayerController extends AbstractController
{
    #[Route('', name: 'get_players', methods: ['GET'])]
    public function index(PlayerRepository $playerRepository): JsonResponse
    {
        $players = $playerRepository->findAll();
        return $this->json($players, Response::HTTP_OK, [], ['groups' => 'player:read']);
    }

    #[Route('/{id}', name: 'get_player', methods: ['GET'])]
    public function show(Player $player): JsonResponse
    {
        return $this->json($player, Response::HTTP_OK, [], ['groups' => 'player:read']);
    }

    #[Route('/add', name: 'create_player', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
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

        // Créez le joueur
        $player = new Player();
        $player->setFirstName($data['firstName']);
        $player->setLastName($data['lastName']);
        $player->setTeam($team);

        $errors = $validator->validate($player);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($player);
        $em->flush();

        return $this->json($player, Response::HTTP_CREATED, [], ['groups' => 'player:read']);
    }

    #[Route('/{id}', name: 'update_player', methods: ['PUT'])]
    public function update(Request $request, Player $player, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        $serializer->deserialize($request->getContent(), Player::class, 'json', ['object_to_populate' => $player]);
        
        $errors = $validator->validate($player);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->flush();

        return $this->json($player, Response::HTTP_OK, [], ['groups' => 'player:read']);
    }

    #[Route('/{id}', name: 'delete_player', methods: ['DELETE'])]
    public function delete(Player $player, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($player);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}