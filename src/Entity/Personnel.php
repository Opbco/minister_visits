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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PersonnelRepository::class)]
#[ORM\Table(name: 'personnel')]
#[ORM\Index(columns: ['nom_complet'], name: 'idx_personnel_nom_prenom')]
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
    #[Groups(['personnel:read', 'personnel:write', 'reunion:read'])]
    private ?string $matricule = null;

    #[ORM\ManyToOne(targetEntity: Fonction::class, inversedBy: 'personnels', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "The function is required.")]
    #[Groups(['personnel:read', 'personnel:write', 'reunion:read'])]
    private ?Fonction $fonction = null; 

    #[ORM\ManyToOne(inversedBy: 'personnels')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "The structure is required.")]
    #[Groups(['personnel:read', 'personnel:write', 'reunion:read'])]
    private ?Structure $structure = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email]
    #[Groups(['personnel:read', 'personnel:write', 'reunion:read'])]
    private ?string $email = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['personnel:read', 'personnel:write', 'reunion:read'])]
    private ?string $telephone = null;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[Groups(['personnel:read', 'personnel:write', 'reunion:read'])]
    private ?User $userAccount = null;

    #[ORM\OneToMany(targetEntity: ReunionParticipation::class, mappedBy: 'personnel', orphanRemoval: true)]
    private Collection $myReunions;

    #[ORM\OneToMany(targetEntity: ActionItem::class, mappedBy: 'responsable')]
    private Collection $actionItems;

    public function __construct()
    {
        $this->myReunions = new ArrayCollection();
        $this->actionItems = new ArrayCollection();
    }

    public function getMeetingStats(): array
    {
        $totalMeetings = $this->myReunions->count();
        $attendedMeetings = 0;

        foreach ($this->myReunions as $participation) {
            if ($participation->isAttended()) {
                $attendedMeetings++;
            }
        }

        return [
            'totalMeetings' => $totalMeetings,
            'attendedMeetings' => $attendedMeetings,
        ];
    }

    public function getPerformance(): array
    {
        $totalActionItems = $this->actionItems->count();
        $completedActionItems = 0;

        foreach ($this->actionItems as $item) {
            if ($item->isCompleted()) {
                $completedActionItems++;
            }
        }

        return [
            'totalActionItems' => $totalActionItems,
            'completedActionItems' => $completedActionItems,
        ];
    }

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

    /**
     * @return Collection<int, ReunionParticipation>
     */
    public function getMyReunions(): Collection
    {
        return $this->myReunions;
    }

    public function addMyReunion(ReunionParticipation $myReunion): static
    {
        if (!$this->myReunions->contains($myReunion)) {
            $this->myReunions->add($myReunion);
            $myReunion->setPersonnel($this);
        }

        return $this;
    }

    public function removeMyReunion(ReunionParticipation $myReunion): static
    {
        if ($this->myReunions->removeElement($myReunion)) {
            // set the owning side to null (unless already changed)
            if ($myReunion->getPersonnel() === $this) {
                $myReunion->setPersonnel(null);
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
            $actionItem->setResponsable($this);
        }

        return $this;
    }

    public function removeActionItem(ActionItem $actionItem): static
    {
        if ($this->actionItems->removeElement($actionItem)) {
            // set the owning side to null (unless already changed)
            if ($actionItem->getResponsable() === $this) {
                $actionItem->setResponsable(null);
            }
        }

        return $this;
    }
}