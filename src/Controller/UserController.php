<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/users', name: 'users_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $users = [
            ['id' => 1, 'name' => 'Alice Dupont', 'email' => 'alice@example.com'],
            ['id' => 2, 'name' => 'Bob Martin', 'email' => 'bob@example.com'],
            ['id' => 3, 'name' => 'Claire Bernard', 'email' => 'claire@example.com'],
        ];

        return $this->json($users);
    }

    #[Route('/users', name: 'users_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['name']) || empty($data['email'])) {
            return $this->json(['error' => 'Les champs name et email sont requis.'], 400);
        }

        $user = [
            'id'    => random_int(100, 999),
            'name'  => $data['name'],
            'email' => $data['email'],
        ];

        return $this->json($user, 201);
    }
}
