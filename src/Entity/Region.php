<?php

namespace App\Entity;

use App\Repository\RegionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: RegionRepository::class)]
class Region
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["region.details", 'reunion:read', "region.list", "division.details", "subdivision.details", "structure.details", "structure.list"])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Groups(["region.details", 'reunion:read', "region.list", "division.details", "subdivision.details", "structure.details", "structure.list"])]
    private ?string $name = null;

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

    #[ORM\OneToMany(mappedBy: 'region', targetEntity: Division::class, orphanRemoval: true)]
    private Collection $divisions;

    public function __construct()
    {
        $this->divisions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->date_created;
    }

    public function setDateCreated(\DateTimeInterface $date_created): self
    {
        $this->date_created = $date_created;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface
    {
        return $this->date_updated;
    }

    public function setDateUpdated(?\DateTimeInterface $date_updated): self
    {
        $this->date_updated = $date_updated;

        return $this;
    }

    public function getUserCreated(): ?User
    {
        return $this->user_created;
    }

    public function setUserCreated(?User $user_created): self
    {
        $this->user_created = $user_created;

        return $this;
    }

    public function getUserUpdated(): ?User
    {
        return $this->user_updated;
    }

    public function setUserUpdated(?User $user_updated): self
    {
        $this->user_updated = $user_updated;

        return $this;
    }

    public function __toString()
    {
        return $this->name ?? 'New Region';
    }

    /**
     * @return Collection<int, Division>
     */
    public function getDivisions(): Collection
    {
        return $this->divisions;
    }

    public function addDivision(Division $division): self
    {
        if (!$this->divisions->contains($division)) {
            $this->divisions->add($division);
            $division->setRegion($this);
        }

        return $this;
    }

    public function removeDivision(Division $division): self
    {
        if ($this->divisions->removeElement($division)) {
            // set the owning side to null (unless already changed)
            if ($division->getRegion() === $this) {
                $division->setRegion(null);
            }
        }

        return $this;
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
}
