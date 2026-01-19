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
#[ORM\Index(columns: ['president_id'], name: 'idx_reunion_president')]
#[ORM\Index(columns: ['organisateur_id'], name: 'idx_reunion_organisateur')]
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
    'type' => 'exact',
    'organisateur' => 'exact',
    'statut' => 'exact',
    'president' => 'exact',
    'salle' => 'exact',
    'participations.status' => 'exact'
])]
#[ApiFilter(DateFilter::class, properties: ['dateDebut', 'dateFin', 'date_created'])]
#[ApiFilter(OrderFilter::class, properties: ['dateDebut', 'objet', 'statut'], arguments: ['orderParameterName' => 'order'])]
class Reunion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['reunion:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The subject (objet) is required.")]
    #[Assert\Length(min: 3, max: 255, minMessage: "Subject must be at least 3 characters", maxMessage: "Subject cannot exceed 255 characters")]
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

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?string $compteRendu = null;

    #[ORM\Column(type: Types::INTEGER, enumType: ReunionStatut::class)]
    #[Assert\NotNull(message: "The status is required.")]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?ReunionStatut $statut = null;
    
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?string $motifReport = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?\DateTimeInterface $nouvelleDateDebut = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?Document $rapport = null;

    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'reunion', cascade: ['persist'])]
    #[Groups(['reunion:read', 'reunion:write'])]
    private Collection $documents;

    #[ORM\ManyToOne]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?User $userValidated = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['reunion:read'])]
    private ?\DateTimeInterface $dateValidation = null;

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

    #[ORM\OneToMany(targetEntity: ReunionParticipation::class, mappedBy: 'reunion', orphanRemoval: true)]
    #[Groups(['reunion:read', 'reunion:write'])]
    private Collection $participations;

    #[ORM\OneToMany(targetEntity: AgendaItem::class, mappedBy: 'reunion', orphanRemoval: true)]
    #[Groups(['reunion:read', 'reunion:write'])]
    private Collection $agendaItems;

    #[ORM\OneToMany(targetEntity: ActionItem::class, mappedBy: 'reunion', orphanRemoval: true)]
    #[Groups(['reunion:read', 'reunion:write'])]
    private Collection $actionItems;

    #[ORM\ManyToOne(targetEntity: MeetingRoom::class)]
    private ?MeetingRoom $salle = null;
    
    // --- COMPUTED PROPERTIES ---
    #[Groups(['reunion:read'])]
    private ?int $dureeMinutes = null;

    #[Groups(['reunion:read'])]
    private ?int $nombreParticipantsInternes = null;

    #[Groups(['reunion:read'])]
    private ?int $nombreParticipantsExternes = null;

    #[Groups(['reunion:read'])]
    private ?int $nombrePresents = null;

    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'reunion', orphanRemoval: true)]
    private Collection $notifications;

    public function __construct()
    {
        $this->date_created = new \DateTimeImmutable();
        $this->documents = new ArrayCollection();
        $this->participations = new ArrayCollection();
        $this->agendaItems = new ArrayCollection();
        $this->actionItems = new ArrayCollection();
        $this->statut = ReunionStatut::PLANNED;
        $this->notifications = new ArrayCollection();
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
        if ($this->statut === null) {
            $this->statut = ReunionStatut::PLANNED;
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

    /**
     * @return Collection<int, ReunionParticipation>
     */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function addParticipation(ReunionParticipation $participation): static
    {
        if (!$this->participations->contains($participation)) {
            $this->participations->add($participation);
            $participation->setReunion($this);
        }

        return $this;
    }

    public function removeParticipation(ReunionParticipation $participation): static
    {
        if ($this->participations->removeElement($participation)) {
            // set the owning side to null (unless already changed)
            if ($participation->getReunion() === $this) {
                $participation->setReunion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AgendaItem>
     */
    public function getAgendaItems(): Collection
    {
        return $this->agendaItems;
    }

    public function addAgendaItem(AgendaItem $agendaItem): static
    {
        if (!$this->agendaItems->contains($agendaItem)) {
            $this->agendaItems->add($agendaItem);
            $agendaItem->setReunion($this);
        }

        return $this;
    }

    public function removeAgendaItem(AgendaItem $agendaItem): static
    {
        if ($this->agendaItems->removeElement($agendaItem)) {
            // set the owning side to null (unless already changed)
            if ($agendaItem->getReunion() === $this) {
                $agendaItem->setReunion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ActionItem>
     */
    public function getActionItems(): Collection
    {
        return $this->actionItems;
    }

    public function addActionItem(ActionItem $actionItem): static
    {
        if (!$this->actionItems->contains($actionItem)) {
            $this->actionItems->add($actionItem);
            $actionItem->setReunion($this);
        }

        return $this;
    }

    public function removeActionItem(ActionItem $actionItem): static
    {
        if ($this->actionItems->removeElement($actionItem)) {
            // set the owning side to null (unless already changed)
            if ($actionItem->getReunion() === $this) {
                $actionItem->setReunion(null);
            }
        }

        return $this;
    }

    
    /**
     * Get all internal personnel participants (regardless of status)
     */
    public function getParticipantsInternes(): array
    {
        return $this->participations
            ->filter(fn($p) => $p->getPersonnel() !== null)
            ->map(fn($p) => $p->getPersonnel())
            ->toArray();
    }

    public function getParticipantsExternes(): array
    {
        return $this->participations
            ->filter(fn($p) => $p->getExternalParticipant() !== null)
            ->map(fn($p) => $p->getExternalParticipant())
            ->toArray();
    }

    /**
     * Get participants who actually attended
     */
    public function getAttendees(): array // Changed from Collection to array for consistency
    {
        return $this->participations
            ->filter(fn($p) => $p->getStatus()->value === 'attended')
            ->toArray();
    }

    // Add method to get invited but not confirmed
    public function getPendingParticipants(): array
    {
        return $this->participations
            ->filter(fn($p) => $p->getStatus()->value === 'invited')
            ->toArray();
    }
    
    // ==================== COMPUTED PROPERTIES ====================

    public function getDureeMinutes(): ?int
    {
        if ($this->dateDebut && $this->dateFin) {
            $diff = $this->dateDebut->diff($this->dateFin);
            return ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
        }
        return null;
    }

    public function getNombreParticipantsInternes(): int
    {
        return $this->getParticipantsInternes() ? count($this->getParticipantsInternes()) : 0;
    }

    public function getNombreParticipantsExternes(): int
    {
        return $this->getParticipantsExternes() ? count($this->getParticipantsExternes()) : 0;
    }

    public function getNombrePresents(): int
    {
        return $this->participations->filter(fn($p) => $p->getStatus() === 'attended')->count();
    }

    // ==================== BUSINESS METHODS ====================

    /**
     * Check if meeting is in the past
     */
    public function isPast(): bool
    {
        return $this->dateFin < new \DateTime();
    }

    /**
     * Check if meeting is ongoing
     */
    public function isOngoing(): bool
    {
        $now = new \DateTime();
        return $this->dateDebut <= $now && $this->dateFin >= $now;
    }

    /**
     * Check if meeting is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->dateDebut > new \DateTime();
    }

    /**
     * Validate the meeting
     */
    public function validate(User $validator): void
    {
        $this->statut = ReunionStatut::CONFIRMED;
        $this->userValidated = $validator;
        $this->dateValidation = new \DateTime();
    }

    /**
     * Postpone the meeting
     */
    public function postpone(\DateTimeInterface $newDate, string $motif): void
    {
        $this->statut = ReunionStatut::POSTPONED;
        $this->nouvelleDateDebut = $newDate;
        $this->motifReport = $motif;
    }

    /**
     * Mark meeting as completed
     */
    public function complete(): void
    {
        $this->statut = ReunionStatut::COMPLETED;
    }

    public function getMotifReport(): ?string
    {
        return $this->motifReport;
    }

    public function setMotifReport(?string $motifReport): static
    {
        $this->motifReport = $motifReport;

        return $this;
    }

    public function getNouvelleDateDebut(): ?\DateTimeInterface
    {
        return $this->nouvelleDateDebut;
    }

    public function setNouvelleDateDebut(?\DateTimeInterface $nouvelleDateDebut): static
    {
        $this->nouvelleDateDebut = $nouvelleDateDebut;

        return $this;
    }

    public function getDateValidation(): ?\DateTimeInterface
    {
        return $this->dateValidation;
    }

    public function setDateValidation(?\DateTimeInterface $dateValidation): static
    {
        $this->dateValidation = $dateValidation;

        return $this;
    }

    public function getSalle(): ?MeetingRoom
    {
        return $this->salle;
    }

    public function setSalle(?MeetingRoom $salle): static
    {
        $this->salle = $salle;

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setReunion($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getReunion() === $this) {
                $notification->setReunion(null);
            }
        }

        return $this;
    }

}