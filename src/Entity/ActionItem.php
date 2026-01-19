<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\ActionStatut;
use App\Repository\ActionItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActionItemRepository::class)]
#[ORM\Table(name: 'action_item')]
#[ApiResource(
    normalizationContext: ['groups' => ['action:read']],
    denormalizationContext: ['groups' => ['action:write']]
)]
class ActionItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['action:read', 'reunion:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'actionItems')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['action:write'])]
    private ?Reunion $reunion = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['action:read', 'action:write', 'reunion:read', 'reunion:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['action:read', 'action:write', 'reunion:read', 'reunion:write'])]
    private ?\DateTimeInterface $dateEcheance = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['action:read', 'action:write', 'reunion:read', 'reunion:write'])]
    private ?string $commentaire = null;

    #[ORM\ManyToOne(inversedBy: 'actionItems')]
    #[Groups(['action:read', 'action:write', 'reunion:read', 'reunion:write'])]
    private ?Personnel $responsable = null;

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

    #[ORM\Column(type: Types::STRING, length: 20, enumType: ActionStatut::class)]
    #[Groups(['action:read', 'action:write', 'reunion:read', 'reunion:write'])]
    private ?ActionStatut $statut = ActionStatut::PENDING;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateEcheance(): ?\DateTimeInterface
    {
        return $this->dateEcheance;
    }

    public function setDateEcheance(?\DateTimeInterface $dateEcheance): static
    {
        $this->dateEcheance = $dateEcheance;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getResponsable(): ?Personnel
    {
        return $this->responsable;
    }

    public function setResponsable(?Personnel $responsable): static
    {
        $this->responsable = $responsable;

        return $this;
    }

    public function getStatut(): ?ActionStatut
    {
        return $this->statut;
    }

    public function setStatut(ActionStatut $statut): static
    {
        $this->statut = $statut;

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
}
