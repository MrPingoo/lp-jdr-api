<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Character $character = null;

    #[ORM\Column(length: 20)]
    private string $status = 'active';

    #[ORM\Column(type: 'json')]
    private array $history = [];

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getCharacter(): ?Character { return $this->character; }
    public function setCharacter(?Character $character): static { $this->character = $character; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function getHistory(): array { return $this->history; }
    public function setHistory(array $history): static { $this->history = $history; return $this; }

    public function addMessage(string $role, string $content): static
    {
        $this->history[] = ['role' => $role, 'content' => $content];
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
