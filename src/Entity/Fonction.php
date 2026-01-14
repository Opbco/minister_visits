<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\FonctionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FonctionRepository::class)]
#[ORM\Table(name: 'fonction')]
#[ApiResource(
    normalizationContext: ['groups' => ['fonction:read']],
    denormalizationContext: ['groups' => ['fonction:write']],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete(),
    ],
    order: ['libelle' => 'ASC']
)]
#[ApiFilter(SearchFilter::class, properties: [
    'libelle' => 'partial',
    'abbreviation' => 'exact'
])]
class Fonction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['fonction:read', 'personnel:read', 'reunion:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The function name is required.")]
    #[Groups(['fonction:read', 'fonction:write', 'personnel:read', 'reunion:read'])]
    private ?string $libelle = null; // e.g. "Directeur des Ressources Humaines"

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['fonction:read', 'fonction:write', 'personnel:read', 'reunion:read'])]
    private ?string $abbreviation = null; // e.g. "DRH"

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['fonction:read', 'fonction:write'])]
    private ?string $description = null;

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

    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(?string $abbreviation): static
    {
        $this->abbreviation = $abbreviation;
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

    public function __toString(): string
    {
        return $this->abbreviation ? sprintf('%s (%s)', $this->libelle, $this->abbreviation) : $this->libelle;
    }
}