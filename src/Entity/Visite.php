<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Repository\VisiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VisiteRepository::class)]
#[ORM\Table(name: 'visite')]
#[ORM\Index(columns: ['date_arrivee'], name: 'idx_visite_date_arrivee')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['visite:read']],
    denormalizationContext: ['groups' => ['visite:write']],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete(),
    ],
    order: ['dateArrivee' => 'DESC']
)]
#[ApiFilter(SearchFilter::class, properties: [
    'structure' => 'exact',       // Find all visits to a specific school
    'evenement' => 'exact',       // Find all visits during a specific event
    'user_created' => 'exact'
])]
#[ApiFilter(DateFilter::class, properties: ['dateArrivee', 'dateDepart'])]
#[ApiFilter(OrderFilter::class, properties: ['dateArrivee'], arguments: ['orderParameterName' => 'order'])]
class Visite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['visite:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID, nullable: true)]
    #[Groups(['visite:read'])]
    private ?string $uuid = null;

    #[ORM\ManyToOne(inversedBy: 'visites')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "The event is required.")]
    #[Groups(['visite:read', 'visite:write'])]
    private ?Evenement $evenement = null;

    #[ORM\ManyToOne(inversedBy: 'visites')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "The structure is required.")]
    #[Groups(['visite:read', 'visite:write'])]
    private ?Structure $structure = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "The arrival date is required.")]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['visite:read', 'visite:write'])]
    private ?\DateTimeInterface $dateArrivee = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\GreaterThanOrEqual(propertyPath: "dateArrivee", message: "The departure date cannot be earlier than the arrival date.")]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['visite:read', 'visite:write'])]
    private ?\DateTimeInterface $dateDepart = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['visite:read', 'visite:write'])]
    private ?string $details = null;

    // Main Report of the visit
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[Groups(['visite:read', 'visite:write'])]
    private ?Document $rapport = null;

    // Additional documents (e.g. photos, attendance sheets)
    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'visite', cascade: ['persist'])]
    #[Groups(['visite:read', 'visite:write'])]
    private Collection $documents;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['visite:read'])]
    private ?\DateTimeInterface $date_created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['visite:read'])]
    private ?\DateTimeInterface $date_updated = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['visite:read', 'visite:write'])]
    private ?User $user_created = null;

    #[ORM\ManyToOne]
    #[Groups(['visite:read', 'visite:write'])]
    private ?User $user_updated = null;

    public function __construct()
    {
        $this->date_created = new \DateTimeImmutable();
        $this->documents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): static
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): static
    {
        $this->evenement = $evenement;
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

    public function getDateArrivee(): ?\DateTimeInterface
    {
        return $this->dateArrivee;
    }

    public function setDateArrivee(?\DateTimeInterface $dateArrivee): static
    {
        $this->dateArrivee = $dateArrivee;
        return $this;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->dateDepart;
    }

    public function setDateDepart(?\DateTimeInterface $dateDepart): static
    {
        $this->dateDepart = $dateDepart;
        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): static
    {
        $this->details = $details;
        return $this;
    }

    public function getRapport(): ?Document
    {
        return $this->rapport;
    }

    public function setRapport(?Document $rapport): static
    {
        $this->rapport = $rapport;
        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setVisite($this);
        }
        return $this;
    }

    public function removeDocument(Document $document): static
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getVisite() === $this) {
                $document->setVisite(null);
            }
        }
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

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if ($this->date_created === null) {
            $this->date_created = new \DateTimeImmutable();
        }
        // Auto-generate UUID if not set
        if ($this->uuid === null) {
            $this->uuid = uniqid();
        }
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->date_updated = new \DateTimeImmutable();
    }
}