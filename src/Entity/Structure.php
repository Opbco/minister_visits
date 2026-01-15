<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
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
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StructureRepository::class)]
#[ORM\Table(name: 'structure')]
#[ORM\Index(columns: ['name_fr'], name: 'idx_structure_name_fr')]
#[ORM\Index(columns: ['name_en'], name: 'idx_structure_name_en')]
#[ORM\Index(columns: ['acronym'], name: 'idx_structure_acronym')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    // Added 'structure.list' to normalizationContext to ensure SubDivision name is visible
    normalizationContext: ['groups' => ['structure:read', 'structure.list'], 'enable_max_depth' => true],
    denormalizationContext: ['groups' => ['structure:write']],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete(),
    ],
    order: ['nameFr' => 'ASC']
)]
#[ApiFilter(SearchFilter::class, properties: [
    'nameFr' => 'partial', 
    'nameEn' => 'partial', 
    'acronym' => 'exact',
    'type' => 'exact',
    'category' => 'exact',
    'subdivision' => 'exact',
    'subdivision.name' => 'partial',
    'parent' => 'exact' // Added parent filter to find all children of a structure
])]
#[ApiFilter(BooleanFilter::class, properties: ['isBilingual', 'hasIndustrial', 'hasCommercial', 'hasAgricultural'])]
#[ApiFilter(OrderFilter::class, properties: ['nameFr', 'date_created'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(DateFilter::class, properties: ['date_created'])]
class Structure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['structure:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The French name is required.")]
    #[Assert\Length(min: 2, max: 255)]
    #[Groups(['structure:read', 'structure:write'])]
    private ?string $nameFr = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['structure:read', 'structure:write'])]
    private ?string $nameEn = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    #[Groups(['structure:read', 'structure:write'])]
    private ?string $acronym = null;

    #[ORM\Column(type: Types::INTEGER, enumType: StructureCategory::class)]
    #[Assert\NotNull(message: "The category is required.")]
    #[Groups(['structure:read', 'structure:write'])]
    private ?StructureCategory $category = null;

    #[ORM\Column(type: Types::STRING, length: 100, enumType: StructureType::class)]
    #[Assert\NotNull(message: "The structure type is required.")]
    #[Groups(['structure:read', 'structure:write'])]
    private ?StructureType $type = StructureType::ETABLISSEMENT;

    #[ORM\Column(type: Types::INTEGER, enumType: StructureRank::class, nullable: true)]
    #[Groups(['structure:read', 'structure:write'])]
    private ?StructureRank $levelRank = StructureRank::Service;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['structure:read', 'structure:write'])]
    private ?string $codeHierarchique = "";

    #[ORM\Column(type: Types::STRING, length: 100, enumType: StructureEducation::class, nullable: true)]
    #[Groups(['structure:read', 'structure:write'])]
    private ?StructureEducation $education = null;

    #[ORM\Column(type: Types::STRING, length: 100, enumType: StructureOrdre::class, nullable: true)]
    #[Groups(['structure:read', 'structure:write'])]
    private ?StructureOrdre $ordre = null;

    #[ORM\Column(type: Types::INTEGER, enumType: Cycle::class, nullable: true)]
    #[Groups(['structure:read', 'structure:write'])]
    private ?Cycle $cycle = null;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['structure:read', 'structure:write'])]
    private ?bool $hasIndustrial = false;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['structure:read', 'structure:write'])]
    private ?bool $hasCommercial = false;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['structure:read', 'structure:write'])]
    private ?bool $hasAgricultural = false;

    #[ORM\ManyToOne(inversedBy: 'structures')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "The subdivision is required.")]
    #[Groups(['structure:read', 'structure:write'])]
    private ?SubDivision $subdivision = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['structure:read', 'structure:write'])]
    private ?string $adress = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    #[Assert\Range(min: -90, max: 90)]
    #[Groups(['structure:read', 'structure:write'])]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    #[Assert\Range(min: -180, max: 180)]
    #[Groups(['structure:read', 'structure:write'])]
    private ?string $longitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    #[Groups(['structure:read', 'structure:write'])]
    private ?string $altitude = null;

    #[ORM\Column(type: Types::STRING, length: 100, enumType: Subsystem::class, nullable: true)]
    #[Groups(['structure:read', 'structure:write'])]
    private ?Subsystem $subsystem = null;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['structure:read', 'structure:write'])]
    private ?bool $isBilingual = false;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['structure:read'])]
    private ?\DateTimeInterface $date_created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['structure:read'])]
    private ?\DateTimeInterface $date_updated = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['structure:read', 'structure:write'])]
    private ?User $user_created = null;

    #[ORM\ManyToOne]
    #[Groups(['structure:read', 'structure:write'])]
    private ?User $user_updated = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'myStructures')]
    #[Groups(['structure:read', 'structure:write'])]
    #[MaxDepth(1)]
    private ?self $parent = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    #[Groups(['structure:read'])]
    #[MaxDepth(1)]
    private Collection $myStructures;

    #[ORM\OneToMany(targetEntity: Visite::class, mappedBy: 'structure')]
    private Collection $visites;

    #[ORM\OneToMany(targetEntity: Reunion::class, mappedBy: 'organisateur', orphanRemoval: true)]
    private Collection $reunions;

    public function __construct()
    {
        $this->date_created = new \DateTimeImmutable();
        $this->hasIndustrial = false;
        $this->hasCommercial = false;
        $this->hasAgricultural = false;
        $this->isBilingual = false;
        $this->myStructures = new ArrayCollection();
        $this->visites = new ArrayCollection();
        $this->reunions = new ArrayCollection();
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

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if ($this->date_created === null) {
            $this->date_created = new \DateTimeImmutable();
        }
        
        if(empty($this->codeHierarchique)) {
            if ($this->type === StructureType::ETABLISSEMENT) {
                $this->codeHierarchique = 'MINESEC/SDEC/DRES/DDES/ETS';
            } elseif ($this->type === StructureType::DRES) {
                $this->codeHierarchique = 'MINESEC/SDEC';
            } elseif ($this->type === StructureType::CABINET) {
                $this->codeHierarchique = '---';
            }elseif ($this->parent !== null) {
                $this->codeHierarchique = $this->parent->getCodeHierarchique() ?  $this->parent->getCodeHierarchique() . '/' . $this->parent->getAcronym() : $this->parent->getAcronym();
            }
        }
    }

    public function getLocationMap(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->date_updated = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        $name = $this->nameFr ?? 'New Structure';
        return $this->acronym ? sprintf('%s (%s)', $name, $this->acronym) : $name;
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
            if ($myStructure->getParent() === $this) {
                $myStructure->setParent(null);
            }
        }
        return $this;
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
            $visite->setStructure($this);
        }

        return $this;
    }

    public function removeVisite(Visite $visite): static
    {
        if ($this->visites->removeElement($visite)) {
            // set the owning side to null (unless already changed)
            if ($visite->getStructure() === $this) {
                $visite->setStructure(null);
            }
        }

        return $this;
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
            $reunion->setOrganisateur($this);
        }

        return $this;
    }

    public function removeReunion(Reunion $reunion): static
    {
        if ($this->reunions->removeElement($reunion)) {
            // set the owning side to null (unless already changed)
            if ($reunion->getOrganisateur() === $this) {
                $reunion->setOrganisateur(null);
            }
        }

        return $this;
    }

    public function getCodeHierarchique(): ?string
    {
        return $this->codeHierarchique;
    }

    public function setCodeHierarchique(?string $codeHierarchique=""): static
    {
        $this->codeHierarchique = $codeHierarchique;

        return $this;
    }
}