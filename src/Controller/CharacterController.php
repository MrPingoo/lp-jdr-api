<?php

namespace App\Controller;

use App\Entity\Character;
use App\Repository\CharacterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/characters')]
class CharacterController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private CharacterRepository $characterRepository,
    ) {}

    #[Route('', name: 'characters_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->getUser();
        $characters = $this->characterRepository->findBy(['user' => $user]);

        return $this->json(array_map($this->serialize(...), $characters));
    }

    #[Route('/{id}', name: 'characters_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $character = $this->characterRepository->find($id);

        if (!$character || $character->getUser() !== $this->getUser()) {
            return $this->json(['message' => 'Personnage non trouvé.'], 404);
        }

        return $this->json($this->serialize($character));
    }

    #[Route('', name: 'characters_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['firstName']) || empty($data['lastName']) || empty($data['class']) || empty($data['race'])) {
            return $this->json(['message' => 'Les champs firstName, lastName, class et race sont requis.'], 400);
        }

        $character = new Character();
        $character->setFirstName($data['firstName']);
        $character->setLastName($data['lastName']);
        $character->setCharacterClass($data['class']);
        $character->setRace($data['race']);
        $character->setStrength((int) ($data['strength'] ?? 10));
        $character->setDexterity((int) ($data['dexterity'] ?? 10));
        $character->setConstitution((int) ($data['constitution'] ?? 10));
        $character->setIntelligence((int) ($data['intelligence'] ?? 10));
        $character->setWisdom((int) ($data['wisdom'] ?? 10));
        $character->setCharisma((int) ($data['charisma'] ?? 10));
        $character->setUser($this->getUser());

        $this->em->persist($character);
        $this->em->flush();

        return $this->json($this->serialize($character), 201);
    }

    #[Route('/{id}', name: 'characters_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $character = $this->characterRepository->find($id);

        if (!$character || $character->getUser() !== $this->getUser()) {
            return $this->json(['message' => 'Personnage non trouvé.'], 404);
        }

        $this->em->remove($character);
        $this->em->flush();

        return $this->json(null, 204);
    }

    private function serialize(Character $character): array
    {
        return [
            'id'           => $character->getId(),
            'firstName'    => $character->getFirstName(),
            'lastName'     => $character->getLastName(),
            'class'        => $character->getCharacterClass(),
            'race'         => $character->getRace(),
            'strength'     => $character->getStrength(),
            'dexterity'    => $character->getDexterity(),
            'constitution' => $character->getConstitution(),
            'intelligence' => $character->getIntelligence(),
            'wisdom'       => $character->getWisdom(),
            'charisma'     => $character->getCharisma(),
        ];
    }
}
