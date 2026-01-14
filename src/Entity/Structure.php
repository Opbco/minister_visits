<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\StructureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use App\Enum\StructureCategory;
use App\Enum\StructureRank;
use App\Enum\StructureType;
use App\Enum\StructureEducation;
use App\Enum\StructureOrdre;
use App\Enum\Subsystem;
use App\Enum\Cycle;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: StructureRepository::class)]
#[ApiResource]
#[ORM\HasLifecycleCallbacks]
class Structure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nameFr = null;

    #[ORM\Column(length: 255)]
    private ?string $nameEn = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $acronym = null;

    #[ORM\Column(type: Types::INTEGER, enumType: StructureCategory::class)]
    private ?StructureCategory $category = null;

    #[ORM\Column(type: Types::STRING, length: 100, enumType: StructureType::class)]
    private ?StructureType $type = StructureType::ETABLISSEMENT;

    #[ORM\Column(type: Types::INTEGER, enumType: StructureRank::class, nullable: true)]
    private ?StructureRank $levelRank = StructureRank::Service;

    #[ORM\Column(type: Types::STRING, length: 100, enumType: StructureEducation::class, nullable: true)]
    private ?StructureEducation $education = null;

    #[ORM\Column(type: Types::STRING, length: 100, enumType: StructureOrdre::class, nullable: true)]
    private ?StructureOrdre $ordre = null;

    #[ORM\Column(type: Types::INTEGER, enumType: Cycle::class, nullable: true)]
    private ?Cycle $cycle = null;

    #[ORM\Column]
    private ?bool $hasIndustrial = null;

    #[ORM\Column]
    private ?bool $hasCommercial = null;

    #[ORM\Column]
    private ?bool $hasAgricultural = null;

    #[ORM\ManyToOne(inversedBy: 'structures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SubDivision $subdivision = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adress = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $altitude = null;

    #[ORM\Column(type: Types::STRING, length: 100, enumType: Subsystem::class, nullable: true)]
    private ?Subsystem $subsystem = null;

    #[ORM\Column]
    private ?bool $isBilingual = null;
    
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

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'myStructures')]
    private ?self $parent = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    private Collection $myStructures;

    public function __construct()
    {
        $this->myStructures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameFr(): ?string
    {
        return $this->nameFr;
    }

    public function setNameFr(string $nameFr): static
    {
        $this->nameFr = $nameFr;

        return $this;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(string $nameEn): static
    {
        $this->nameEn = $nameEn;

        return $this;
    }

    public function getAcronym(): ?string
    {
        return $this->acronym;
    }

    public function setAcronym(?string $acronym): static
    {
        $this->acronym = $acronym;

        return $this;
    }

    public function getCategory(): ?StructureCategory
    {
        return $this->category;
    }

    public function setCategory(?StructureCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getType(): ?StructureType
    {
        return $this->type;
    }

    public function setType(StructureType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLevelRank(): ?StructureRank
    {
        return $this->levelRank;
    }

    public function setLevelRank(?StructureRank $levelRank): static
    {
        $this->levelRank = $levelRank;

        return $this;
    }

    public function getEducation(): ?StructureEducation
    {
        return $this->education;
    }

    public function setEducation(?StructureEducation $education): static
    {
        $this->education = $education;

        return $this;
    }

    public function getOrdre(): ?StructureOrdre
    {
        return $this->ordre;
    }

    public function setOrdre(?StructureOrdre $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getCycle(): ?Cycle
    {
        return $this->cycle;
    }

    public function setCycle(?Cycle $cycle): static
    {
        $this->cycle = $cycle;

        return $this;
    }

    public function isHasIndustrial(): ?bool
    {
        return $this->hasIndustrial;
    }

    public function setHasIndustrial(bool $hasIndustrial): static
    {
        $this->hasIndustrial = $hasIndustrial;

        return $this;
    }

    public function isHasCommercial(): ?bool
    {
        return $this->hasCommercial;
    }

    public function setHasCommercial(bool $hasCommercial): static
    {
        $this->hasCommercial = $hasCommercial;

        return $this;
    }

    public function isHasAgricultural(): ?bool
    {
        return $this->hasAgricultural;
    }

    public function setHasAgricultural(bool $hasAgricultural): static
    {
        $this->hasAgricultural = $hasAgricultural;

        return $this;
    }

    public function getSubdivision(): ?SubDivision
    {
        return $this->subdivision;
    }

    public function setSubdivision(?SubDivision $subdivision): static
    {
        $this->subdivision = $subdivision;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(?string $adress): static
    {
        $this->adress = $adress;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getAltitude(): ?string
    {
        return $this->altitude;
    }

    public function setAltitude(?string $altitude): static
    {
        $this->altitude = $altitude;

        return $this;
    }

    public function getSubsystem(): ?Subsystem
    {
        return $this->subsystem;
    }

    public function setSubsystem(?Subsystem $subsystem): static
    {
        $this->subsystem = $subsystem;

        return $this;
    }

    public function isIsBilingual(): ?bool
    {
        return $this->isBilingual;
    }

    public function setIsBilingual(bool $isBilingual): static
    {
        $this->isBilingual = $isBilingual;

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

    public function __toString(): string
    {
        return $this->nameFr ?? 'New Structure';
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->date_created = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->date_updated = new \DateTimeImmutable();
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getMyStructures(): Collection
    {
        return $this->myStructures;
    }

    public function addMyStructure(self $myStructure): static
    {
        if (!$this->myStructures->contains($myStructure)) {
            $this->myStructures->add($myStructure);
            $myStructure->setParent($this);
        }

        return $this;
    }

    public function removeMyStructure(self $myStructure): static
    {
        if ($this->myStructures->removeElement($myStructure)) {
            // set the owning side to null (unless already changed)
            if ($myStructure->getParent() === $this) {
                $myStructure->setParent(null);
            }
        }

        return $this;
    }
}