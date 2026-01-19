<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\ParticipantStatut;
use App\Repository\ReunionParticipationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ReunionParticipationRepository::class)]
#[ORM\Table(name: 'reunion_participation')]
#[ORM\Index(columns: ['reunion_id', 'personnel_id'], name: 'idx_participation_reunion_personnel')]
#[ORM\Index(columns: ['reunion_id', 'external_participant_id'], name: 'idx_participation_reunion_external_participant')]
#[ORM\Index(columns: ['status'], name: 'idx_participation_status')]
#[ORM\UniqueConstraint(
    name: 'unique_reunion_personnel',
    columns: ['reunion_id', 'personnel_id']
)]
#[ORM\UniqueConstraint(
    name: 'unique_reunion_external',
    columns: ['reunion_id', 'external_participant_id']
)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource]
class ReunionParticipation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['reunion:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'participations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?Reunion $reunion = null;

    // --- OPTION A: Internal Personnel ---
    #[ORM\ManyToOne(inversedBy: 'myReunions')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['reunion:read', 'reunion:write', 'participation:read'])]
    private ?Personnel $personnel = null;

    // --- OPTION B: External Participant ---
    #[ORM\ManyToOne(targetEntity: ExternalParticipant::class, inversedBy: 'myReunions')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['reunion:read', 'reunion:write', 'participation:read'])]
    private ?ExternalParticipant $externalParticipant = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?\DateTimeImmutable $confirmedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?string $absenceReason = null;

    #[ORM\Column(type: Types::STRING, enumType: ParticipantStatut::class)]
    #[Groups(['reunion:read', 'reunion:write'])]
    private ?ParticipantStatut $status = ParticipantStatut::Invited;

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

    #[ORM\OneToMany(targetEntity: AgendaItem::class, mappedBy: 'presentateur')]
    private Collection $agendaItems;

    public function __construct()
    {
        $this->date_created = new \DateTimeImmutable();
        $this->status = ParticipantStatut::Invited;
        $this->agendaItems = new ArrayCollection();
    }

    // --- Validation to ensure consistency ---
    #[Assert\Callback]
    public function validateParticipant(ExecutionContextInterface $context, $payload): void
    {
        if (null === $this->personnel && null === $this->externalParticipant) {
            $context->buildViolation('Vous devez sélectionner soit un Personnel, soit un Participant Externe.')
                ->atPath('personnel')
                ->addViolation();
        }

        if (null !== $this->personnel && null !== $this->externalParticipant) {
            $context->buildViolation('Un participant ne peut pas être à la fois Personnel et Externe.')
                ->atPath('personnel')
                ->addViolation();
        }
    }

    // --- Helper for Display ---
    public function getParticipantName(): string
    {
        if ($this->personnel) {
            return (string) $this->personnel;
        }
        if ($this->externalParticipant) {
            return sprintf('%s (%s)', $this->externalParticipant->getNom(), $this->externalParticipant->getOrganisation());
        }
        return 'Inconnu';
    }

    #[Groups(['reunion:read', 'participation:read'])]
    public function getParticipantType(): string
    {
        return $this->personnel ? 'internal' : 'external';
    }

    public function __toString(): string
    {
        return $this->getParticipantName();
    }

    // --- Getters & Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReunion(): ?Reunion
    {
        return $this->reunion;
    }

    public function setReunion(?Reunion $reunion): static
    {
        $this->reunion = $reunion;
        return $this;
    }

    public function getPersonnel(): ?Personnel
    {
        return $this->personnel;
    }

    public function setPersonnel(?Personnel $personnel): static
    {
        $this->personnel = $personnel;
        // If setting personnel, verify external is null (logic handled by validation, but good practice to clear)
        if ($personnel !== null) {
            $this->externalParticipant = null;
        }
        return $this;
    }

    public function getExternalParticipant(): ?ExternalParticipant
    {
        return $this->externalParticipant;
    }

    public function setExternalParticipant(?ExternalParticipant $externalParticipant): static
    {
        $this->externalParticipant = $externalParticipant;
        // If setting external, clear personnel
        if ($externalParticipant !== null) {
            $this->personnel = null;
        }
        return $this;
    }

    public function getConfirmedAt(): ?\DateTimeImmutable
    {
        return $this->confirmedAt;
    }

    public function setConfirmedAt(?\DateTimeImmutable $confirmedAt): static
    {
        $this->confirmedAt = $confirmedAt;
        return $this;
    }

    public function getAbsenceReason(): ?string
    {
        return $this->absenceReason;
    }

    public function setAbsenceReason(?string $absenceReason): static
    {
        $this->absenceReason = $absenceReason;
        return $this;
    }

    public function getStatus(): ?ParticipantStatut
    {
        return $this->status;
    }

    public function setStatus(ParticipantStatut $status): static
    {
        $this->status = $status;
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
            $agendaItem->setPresentateur($this);
        }

        return $this;
    }

    public function removeAgendaItem(AgendaItem $agendaItem): static
    {
        if ($this->agendaItems->removeElement($agendaItem)) {
            // set the owning side to null (unless already changed)
            if ($agendaItem->getPresentateur() === $this) {
                $agendaItem->setPresentateur(null);
            }
        }

        return $this;
    }

    public function confirm(): static
    {
        $this->status = ParticipantStatut::Confirmed;
        $this->confirmedAt = new \DateTimeImmutable();
        return $this;
    }

    public function markAsAttended(): static
    {
        $this->status = ParticipantStatut::Attended;
        return $this;
    }

    public function markAsAbsent(string $reason = null): static
    {
        $this->status = ParticipantStatut::Absent;
        $this->absenceReason = $reason;
        return $this;
    }

    public function excuse(string $reason): static
    {
        $this->status = ParticipantStatut::Excused;
        $this->absenceReason = $reason;
        return $this;
    }
}