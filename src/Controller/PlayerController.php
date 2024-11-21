<?php

namespace App\Controller;

use App\Entity\Player;
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
        $player = $serializer->deserialize($request->getContent(), Player::class, 'json');
        
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