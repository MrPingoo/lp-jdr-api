<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAPIController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient,
    ) {}

    #[Route('/chat', name: 'chat_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['info'])) {
            return $this->json(['error' => 'Les champs info est requis.'], 400);
        }

        $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getParameter('app.openai_api_key'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Tu es un maître du jeu pour un jeu de rôle style Donjons et Dragons. Réponds de manière immersive et narrative.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $data['info'],
                    ],
                ],
            ],
        ]);

        $result = $response->toArray();

        $answer = $result['choices'][0]['message']['content'] ?? 'Aucune réponse générée.';

        return $this->json(['response' => $answer], 201);
    }
}
