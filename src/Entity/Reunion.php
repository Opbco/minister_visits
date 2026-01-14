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
use App\Repository\ReunionRepository;
use App\Enum\ReunionStatut;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReunionRepository::class)]
#[ORM\Table(name: 'reunion')]
#[ORM\Index(columns: ['date_debut'], name: 'idx_reunion_date_debut')]
#[ORM\Index(columns: ['statut'], name: 'idx_reunion_statut')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['reunion:read']],
    denormalizationContext: ['groups' => ['reunion:write']],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete(),
    ],
    order: ['dateDebut' => 'DESC']
)]
#[ApiFilter(SearchFilter::class, properties: [
    'objet' => 'partial',
    'lieu' => 'partial',
    'organisateur' => 'exact',
    'statut' => 'exact',
    'president' => 'exact',         // Filter by President (Personnel ID)
    'participantsInternes' => 'exact' // Filter meetings where a specific staff member participated
])]
#[ApiFilter(DateFilter::class, properties: ['dateDebut', 'dateFin'])]
#[ApiFilter(OrderFilter::class, properties: ['dateDebut', 'objet'], arguments: ['orderParameterName' => 'order'])]
class Reunion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['reunion:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The subject (objet) is required.")]
    #[Assert\Length(min: 3, max: 255)]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?string $objet = null;
    
    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "The start date is required.")]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "The end date is required.")]
    #[Assert\GreaterThanOrEqual(propertyPath: "dateDebut", message: "The end date cannot be earlier than the start date.")]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?string $lieu = null;

    #[ORM\ManyToOne(inversedBy: 'reunions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "The organizer structure is required.")]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?Structure $organisateur = null;

    // --- UPDATED: Linked to Personnel Entity ---
    #[ORM\ManyToOne]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?Personnel $president = null;

    // --- UPDATED: Internal Staff Participants ---
    #[ORM\ManyToMany(targetEntity: Personnel::class)]
    #[Groups(['reunion:read', 'reunion:write'])]
    private Collection $participantsInternes;

    // --- UPDATED: External Consultants/Guests ---
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?string $participantsExternes = null; 

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?string $compteRendu = null;

    #[ORM\Column(type: 'string', enumType: ReunionStatut::class)]
    #[Assert\NotNull(message: "The status is required.")]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?ReunionStatut $statut = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?string $motifRejet = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?Document $rapport = null;

    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'reunion', cascade: ['persist'])]
    #[Groups(['reunion:read', 'reunion:write'])]
    private Collection $documents;

    #[ORM\ManyToOne]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?User $userValidated = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['reunion:read'])]
    private ?\DateTimeInterface $date_created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['reunion:read'])]
    private ?\DateTimeInterface $date_updated = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?User $user_created = null;

    #[ORM\ManyToOne]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?User $user_updated = null;

    public function __construct()
    {
        $this->date_created = new \DateTimeImmutable();
        $this->documents = new ArrayCollection();
        $this->participantsInternes = new ArrayCollection();
    }

    // ... Standard Getters/Setters for ID, Objet, Dates, Lieu, Organisateur ...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObjet(): ?string
    {
        return $this->objet;
    }

    public function setObjet(string $objet): static
    {
        $this->objet = $objet;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): static
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getOrganisateur(): ?Structure
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?Structure $organisateur): static
    {
        $this->organisateur = $organisateur;
        return $this;
    }

    // --- NEW GETTERS/SETTERS FOR PERSONNEL ---

    public function getPresident(): ?Personnel
    {
        return $this->president;
    }

    public function setPresident(?Personnel $president): static
    {
        $this->president = $president;
        return $this;
    }

    /**
     * @return Collection<int, Personnel>
     */
    public function getParticipantsInternes(): Collection
    {
        return $this->participantsInternes;
    }

    public function addParticipantsInterne(Personnel $participantsInterne): static
    {
        if (!$this->participantsInternes->contains($participantsInterne)) {
            $this->participantsInternes->add($participantsInterne);
        }
        return $this;
    }

    public function removeParticipantsInterne(Personnel $participantsInterne): static
    {
        $this->participantsInternes->removeElement($participantsInterne);
        return $this;
    }

    public function getParticipantsExternes(): ?string
    {
        return $this->participantsExternes;
    }

    public function setParticipantsExternes(?string $participantsExternes): static
    {
        $this->participantsExternes = $participantsExternes;
        return $this;
    }

    // ... Remaining Getters/Setters (CompteRendu, Statut, Documents, Users, Dates) ...

    public function getCompteRendu(): ?string
    {
        return $this->compteRendu;
    }

    public function setCompteRendu(?string $compteRendu): static
    {
        $this->compteRendu = $compteRendu;
        return $this;
    }

    public function getStatut(): ?ReunionStatut
    {
        return $this->statut;
    }

    public function setStatut(ReunionStatut $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getMotifRejet(): ?string
    {
        return $this->motifRejet;
    }

    public function setMotifRejet(?string $motifRejet): static
    {
        $this->motifRejet = $motifRejet;
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

    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setReunion($this);
        }
        return $this;
    }

    public function removeDocument(Document $document): static
    {
        if ($this->documents->removeElement($document)) {
            if ($document->getReunion() === $this) {
                $document->setReunion(null);
            }
        }
        return $this;
    }

    public function getUserValidated(): ?User
    {
        return $this->userValidated;
    }

    public function setUserValidated(?User $userValidated): static
    {
        $this->userValidated = $userValidated;
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
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->date_updated = new \DateTimeImmutable();
    }
    
    public function __toString(): string
    {
        return $this->objet ?? 'New Meeting';
    }
}