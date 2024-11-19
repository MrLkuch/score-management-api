<?php

namespace App\Controller;

use App\Entity\Player;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/players')]
class PlayerController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $players = $entityManager->getRepository(Player::class)->findAll();
        return $this->json($players);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $player = $serializer->deserialize($request->getContent(), Player::class, 'json');
        $entityManager->persist($player);
        $entityManager->flush();
        return $this->json($player, 201);
    }

    #[Route('/api/players', name: 'get_players', methods: ['GET'])]
    public function getPlayers(PlayerRepository $playerRepository): JsonResponse
    {
        $players = $playerRepository->findAll();
        return $this->json($players, 200, [], ['groups' => 'player:read']);
    }

    // Ajoutez les méthodes pour GET (single), PUT, DELETE
}