<?php

namespace App\Entity;

use App\Repository\CharacterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CharacterRepository::class)]
#[ORM\Table(name: '`character`')]
class Character
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(name: '`class`', length: 50)]
    private ?string $characterClass = null;

    #[ORM\Column(length: 50)]
    private ?string $race = null;

    #[ORM\Column]
    private int $strength = 10;

    #[ORM\Column]
    private int $dexterity = 10;

    #[ORM\Column]
    private int $constitution = 10;

    #[ORM\Column]
    private int $intelligence = 10;

    #[ORM\Column]
    private int $wisdom = 10;

    #[ORM\Column]
    private int $charisma = 10;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int { return $this->id; }

    public function getFirstName(): ?string { return $this->firstName; }
    public function setFirstName(string $firstName): static { $this->firstName = $firstName; return $this; }

    public function getLastName(): ?string { return $this->lastName; }
    public function setLastName(string $lastName): static { $this->lastName = $lastName; return $this; }

    public function getCharacterClass(): ?string { return $this->characterClass; }
    public function setCharacterClass(string $characterClass): static { $this->characterClass = $characterClass; return $this; }

    public function getRace(): ?string { return $this->race; }
    public function setRace(string $race): static { $this->race = $race; return $this; }

    public function getStrength(): int { return $this->strength; }
    public function setStrength(int $strength): static { $this->strength = $strength; return $this; }

    public function getDexterity(): int { return $this->dexterity; }
    public function setDexterity(int $dexterity): static { $this->dexterity = $dexterity; return $this; }

    public function getConstitution(): int { return $this->constitution; }
    public function setConstitution(int $constitution): static { $this->constitution = $constitution; return $this; }

    public function getIntelligence(): int { return $this->intelligence; }
    public function setIntelligence(int $intelligence): static { $this->intelligence = $intelligence; return $this; }

    public function getWisdom(): int { return $this->wisdom; }
    public function setWisdom(int $wisdom): static { $this->wisdom = $wisdom; return $this; }

    public function getCharisma(): int { return $this->charisma; }
    public function setCharisma(int $charisma): static { $this->charisma = $charisma; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }
}
