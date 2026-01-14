<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Repository\PersonnelRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PersonnelRepository::class)]
#[ORM\Table(name: 'personnel')]
#[ORM\Index(columns: ['nomComplet'], name: 'idx_personnel_nom_prenom')]
#[ORM\Index(columns: ['matricule'], name: 'idx_personnel_matricule')]
#[ApiResource(
    normalizationContext: ['groups' => ['personnel:read']],
    denormalizationContext: ['groups' => ['personnel:write']],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete(),
    ],
    order: ['nomComplet' => 'ASC']
)]
#[ApiFilter(SearchFilter::class, properties: [
    'nomComplet' => 'partial',
    'matricule' => 'exact',
    'structure' => 'exact',
    'fonction' => 'exact',      // Filter by Function ID
    'fonction.libelle' => 'partial' // Filter by Function Name (e.g., search "Directeur")
])]
#[ApiFilter(OrderFilter::class, properties: ['nomComplet', 'structure.nameFr'], arguments: ['orderParameterName' => 'order'])]
class Personnel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['personnel:read', 'reunion:read', 'structure:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The name is required.")]
    #[Groups(['personnel:read', 'personnel:write', 'reunion:read'])]
    private ?string $nomComplet = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['personnel:read', 'personnel:write'])]
    private ?string $matricule = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "The function is required.")]
    #[Groups(['personnel:read', 'personnel:write', 'reunion:read'])]
    private ?Fonction $fonction = null; 

    #[ORM\ManyToOne(inversedBy: 'personnels')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "The structure is required.")]
    #[Groups(['personnel:read', 'personnel:write'])]
    private ?Structure $structure = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email]
    #[Groups(['personnel:read', 'personnel:write'])]
    private ?string $email = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['personnel:read', 'personnel:write'])]
    private ?string $telephone = null;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[Groups(['personnel:read', 'personnel:write'])]
    private ?User $userAccount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomComplet(): ?string
    {
        return $this->nomComplet;
    }

    public function setNomComplet(string $nom): static
    {
        $this->nomComplet = strtoupper($nom);
        return $this;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(?string $matricule): static
    {
        $this->matricule = $matricule;
        return $this;
    }

    public function getFonction(): ?Fonction
    {
        return $this->fonction;
    }

    public function setFonction(?Fonction $fonction): static
    {
        $this->fonction = $fonction;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getUserAccount(): ?User
    {
        return $this->userAccount;
    }

    public function setUserAccount(?User $userAccount): static
    {
        $this->userAccount = $userAccount;
        return $this;
    }

    public function __toString(): string
    {
        $func = $this->fonction ? $this->fonction->getLibelle() : 'N/A';
        return sprintf('%s (%s)', $this->nomComplet, $func);
    }
}