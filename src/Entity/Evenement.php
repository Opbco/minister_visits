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
use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ORM\Table(name: 'evenement')]
#[ORM\Index(columns: ['date_debut'], name: 'idx_evenement_date_debut')] // Index for chronological sorting
#[ORM\Index(columns: ['libelle'], name: 'idx_evenement_libelle')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['evenement:read']],
    denormalizationContext: ['groups' => ['evenement:write']],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete(),
    ],
    order: ['dateDebut' => 'DESC'] // Default sort: newest events first
)]
#[ApiFilter(SearchFilter::class, properties: [
    'libelle' => 'partial', 
    'theme' => 'partial',
    'user_created.username' => 'exact' // Filter events created by a specific user
])]
#[ApiFilter(DateFilter::class, properties: ['dateDebut', 'dateFin', 'date_created'])]
#[ApiFilter(OrderFilter::class, properties: ['dateDebut', 'libelle'], arguments: ['orderParameterName' => 'order'])]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['evenement:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The label (libelle) is required.")]
    #[Assert\Length(min: 3, max: 255)]
    #[Groups(['evenement:read', 'evenement:write'])]
    private ?string $libelle = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['evenement:read', 'evenement:write'])]
    private ?string $theme = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['evenement:read', 'evenement:write'])]
    private ?string $objectifs = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "The start date is required.")]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['evenement:read', 'evenement:write'])]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\GreaterThanOrEqual(propertyPath: "dateDebut", message: "The end date cannot be earlier than the start date.")]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['evenement:read', 'evenement:write'])]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['evenement:read'])]
    private ?\DateTimeInterface $date_created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['evenement:read'])]
    private ?\DateTimeInterface $date_updated = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['evenement:read', 'evenement:write'])]
    private ?User $user_created = null;

    #[ORM\ManyToOne]
    #[Groups(['evenement:read', 'evenement:write'])]
    private ?User $user_updated = null;

    #[ORM\OneToMany(targetEntity: Visite::class, mappedBy: 'evenement', orphanRemoval: true)]
    private Collection $visites;

    public function __construct()
    {
        $this->date_created = new \DateTimeImmutable();
        $this->visites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(?string $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function getObjectifs(): ?string
    {
        return $this->objectifs;
    }

    public function setObjectifs(?string $objectifs): static
    {
        $this->objectifs = $objectifs;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

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
        return $this->libelle ?? 'New Event';
    }

    /**
     * @return Collection<int, Visite>
     */
    public function getVisites(): Collection
    {
        return $this->visites;
    }

    public function addVisite(Visite $visite): static
    {
        if (!$this->visites->contains($visite)) {
            $this->visites->add($visite);
            $visite->setEvenement($this);
        }

        return $this;
    }

    public function removeVisite(Visite $visite): static
    {
        if ($this->visites->removeElement($visite)) {
            // set the owning side to null (unless already changed)
            if ($visite->getEvenement() === $this) {
                $visite->setEvenement(null);
            }
        }

        return $this;
    }
}