<?php

namespace App\Controller;

use App\Entity\Game;
use App\Repository\CharacterRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/api/games')]
class GameController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private GameRepository $gameRepository,
        private CharacterRepository $characterRepository,
        private HttpClientInterface $httpClient,
    ) {}

    #[Route('', name: 'games_start', methods: ['POST'])]
    public function start(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['characterId'])) {
            return $this->json(['message' => 'Le champ characterId est requis.'], 400);
        }

        $character = $this->characterRepository->find($data['characterId']);

        if (!$character || $character->getUser() !== $this->getUser()) {
            return $this->json(['message' => 'Personnage non trouvé.'], 404);
        }

        $introduction = sprintf(
            'Bienvenue, %s %s ! Vous êtes un(e) %s %s. Votre aventure commence aux portes d\'un donjon ancien et mystérieux. Les murs de pierre suintent d\'humidité, et une odeur de soufre flotte dans l\'air. Devant vous, un couloir sombre s\'enfonce dans les profondeurs de la terre. Que faites-vous ?',
            $character->getFirstName(),
            $character->getLastName(),
            $character->getCharacterClass(),
            $character->getRace(),
        );

        $game = new Game();
        $game->setCharacter($character);
        $game->addMessage('assistant', $introduction);

        $this->em->persist($game);
        $this->em->flush();

        return $this->json(['id' => $game->getId(), 'introduction' => $introduction], 201);
    }

    #[Route('/{id}/message', name: 'games_message', methods: ['POST'])]
    public function message(int $id, Request $request): JsonResponse
    {
        $game = $this->gameRepository->find($id);

        if (!$game || $game->getCharacter()->getUser() !== $this->getUser()) {
            return $this->json(['message' => 'Partie non trouvée.'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['message'])) {
            return $this->json(['message' => 'Le champ message est requis.'], 400);
        }

        $userMessage = $data['message'];
        $character = $game->getCharacter();

        $game->addMessage('user', $userMessage);

        $systemPrompt = sprintf(
            'Tu es un maître du jeu pour un jeu de rôle style Donjons et Dragons en français. ' .
            'Le joueur incarne %s %s, un(e) %s %s avec les statistiques suivantes : ' .
            'Force %d, Dextérité %d, Constitution %d, Intelligence %d, Sagesse %d, Charisme %d. ' .
            'Réponds de manière immersive, narrative et en français. Sois cohérent avec les statistiques du personnage.',
            $character->getFirstName(),
            $character->getLastName(),
            $character->getCharacterClass(),
            $character->getRace(),
            $character->getStrength(),
            $character->getDexterity(),
            $character->getConstitution(),
            $character->getIntelligence(),
            $character->getWisdom(),
            $character->getCharisma(),
        );

        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($game->getHistory() as $entry) {
            $messages[] = ['role' => $entry['role'], 'content' => $entry['content']];
        }

        $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getParameter('app.openai_api_key'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4o',
                'messages' => $messages,
            ],
        ]);

        $result = $response->toArray();
        $answer = $result['choices'][0]['message']['content'] ?? 'Aucune réponse générée.';

        $game->addMessage('assistant', $answer);
        $this->em->flush();

        return $this->json(['response' => $answer]);
    }

    #[Route('/{id}/roll', name: 'games_roll', methods: ['POST'])]
    public function roll(int $id, Request $request): JsonResponse
    {
        $game = $this->gameRepository->find($id);

        if (!$game || $game->getCharacter()->getUser() !== $this->getUser()) {
            return $this->json(['message' => 'Partie non trouvée.'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $diceType = (int) ($data['diceType'] ?? 20);

        if ($diceType < 2) {
            $diceType = 20;
        }

        $result = random_int(1, $diceType);

        return $this->json(['result' => $result, 'diceType' => $diceType]);
    }
}
