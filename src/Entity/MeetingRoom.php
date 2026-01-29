<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\MeetingRoomRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: MeetingRoomRepository::class)]
#[ORM\Table(name: 'meeting_room')]
#[ApiResource(
    normalizationContext: ['groups' => ['room:read']],
    denormalizationContext: ['groups' => ['room:write']],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete(),
    ],
    order: ['name' => 'ASC']
)]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial', 'equipment' => 'partial'])]
#[ORM\HasLifecycleCallbacks]
class MeetingRoom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['room:read', 'reunion:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Structure::class)]
    private ?Structure $structure = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de la salle est obligatoire.")]
    #[Groups(['room:read', 'room:write', 'reunion:read'])]
    private ?string $nom = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\Positive]
    #[Groups(['room:read', 'room:write', 'reunion:read'])]
    private ?int $capacite = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['room:read', 'room:write', 'reunion:read'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['room:read', 'room:write', 'reunion:read'])]
    private array $equipements = []; // e.g. ["Projector", "Video Conf", "AC"]

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    private ?\DateTimeInterface $date_created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    private ?\DateTimeInterface $date_updated = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user_created = null;

    #[ORM\ManyToOne]
    private ?User $user_updated = null;

    #[ORM\OneToMany(targetEntity: Reunion::class, mappedBy: 'salle', orphanRemoval: true, cascade: ['persist', 'remove'])]
    #[Groups(['room:read', 'room:write'])]
    private Collection $reunions;

    public function __construct()
    {
        $this->reunions = new ArrayCollection();
    }
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    public function setStructure(?Structure $structure): static
    {
        $this->structure = $structure;

        return $this;
    }

    public function getCapacite(): ?int
    {
        return $this->capacite;
    }

    public function setCapacite(?int $capacite): static
    {
        $this->capacite = $capacite;

        return $this;
    }

    public function getEquipements(): ?array
    {
        return $this->equipements;
    }

    public function setEquipements(?array $equipements): static
    {
        $this->equipements = $equipements ?? [];

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->date_created;
    }

    public function setDateCreated(\DateTimeInterface $date_created): static
    {
        $this->date_created = $date_created;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface
    {
        return $this->date_updated;
    }

    public function setDateUpdated(?\DateTimeInterface $date_updated): static
    {
        $this->date_updated = $date_updated;

        return $this;
    }

    public function getUserCreated(): ?User
    {
        return $this->user_created;
    }

    public function setUserCreated(?User $user_created): static
    {
        $this->user_created = $user_created;

        return $this;
    }

    public function getUserUpdated(): ?User
    {
        return $this->user_updated;
    }

    public function setUserUpdated(?User $user_updated): static
    {
        $this->user_updated = $user_updated;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->date_created = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->date_updated = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->nom ?? 'New Meeting Room';
    }

    public function isAvailable(): bool
    {
        $now = new \DateTimeImmutable();

        foreach ($this->reunions as $reunion) {
            if ($reunion->getStartTime() <= $now && $reunion->getEndTime() >= $now) {
                return false;
            }
        }

        return true;
    }

    public function getUsageStats(): array
    {
        $totalMeetings = count($this->reunions);
        $totalHours = 0;

        foreach ($this->reunions as $reunion) {
            $interval = $reunion->getStartTime()->diff($reunion->getEndTime());
            $totalHours += ($interval->h + ($interval->days * 24)) + ($interval->i / 60);
        }

        return [
            'total_meetings' => $totalMeetings,
            'total_hours' => round($totalHours, 2),
        ];
    }

    /**
     * @return Collection<int, Reunion>
     */
    public function getReunions(): Collection
    {
        return $this->reunions;
    }

    public function addReunion(Reunion $reunion): static
    {
        if (!$this->reunions->contains($reunion)) {
            $this->reunions->add($reunion);
            $reunion->setSalle($this);
        }

        return $this;
    }

    public function removeReunion(Reunion $reunion): static
    {
        if ($this->reunions->removeElement($reunion)) {
            // set the owning side to null (unless already changed)
            if ($reunion->getSalle() === $this) {
                $reunion->setSalle(null);
            }
        }

        return $this;
    }
}
