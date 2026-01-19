<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AgendaItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AgendaItemRepository::class)]
#[ORM\Table(name: 'agenda_item')]
#[ApiResource(
    normalizationContext: ['groups' => ['agenda:read']],
    denormalizationContext: ['groups' => ['agenda:write']]
)]
class AgendaItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['agenda:read', 'reunion:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'agendaItems')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['agenda:write'])]
    private ?Reunion $reunion = null;

    #[ORM\Column]
    #[Groups(['agenda:read', 'agenda:write', 'reunion:read', 'reunion:write'])]
    private ?int $ordre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['agenda:read', 'agenda:write', 'reunion:read', 'reunion:write'])]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['agenda:read', 'agenda:write', 'reunion:read', 'reunion:write'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['agenda:read', 'agenda:write', 'reunion:read', 'reunion:write'])]
    private ?int $dureeEstimee = null;

    #[ORM\ManyToOne(inversedBy: 'agendaItems')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['agenda:read', 'agenda:write', 'reunion:read', 'reunion:write'])]
    private ?ReunionParticipation $presentateur = null;

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

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

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

    public function getDureeEstimee(): ?int
    {
        return $this->dureeEstimee;
    }

    public function setDureeEstimee(?int $dureeEstimee): static
    {
        $this->dureeEstimee = $dureeEstimee;

        return $this;
    }

    public function getPresentateur(): ?ReunionParticipation
    {
        return $this->presentateur;
    }

    public function setPresentateur(?ReunionParticipation $presentateur): static
    {
        $this->presentateur = $presentateur;

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
