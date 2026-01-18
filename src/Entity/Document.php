<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Document
{
    const SERVER_PATH_TO_FILES_FOLDER = __DIR__ . '/../../public/uploads';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["document.list", 'bulletin:read', 'classe.timetable', 'etablissement.details', "document.details", 'paiement.list', "depense.details", "depense.list", 'salleclasse.student', 'user.details'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true, nullable: true)]
    #[Groups(["document.list", "document.details"])]
    private ?string $fileName = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updated = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    #[Groups(["document.list"])]
    private ?int $fileSize = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["document.list"])]
    private ?string $originalFileName = null;

    #[Assert\File(
        maxSize: '2M',
        mimeTypes: ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        mimeTypesMessage: 'Please upload a valid document (PDF, Word, JPEG, PNG)'
    )]
    private ?UploadedFile $file = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(["document.list"])]
    private ?string $mimeType = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $context = null;

    #[ORM\ManyToOne(inversedBy: 'photos')]
    private ?Visite $visite = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?Reunion $reunion = null;

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setContext(?string $context): self
    {
        $this->context = $context;
        return $this;
    }


    public function setFile(?UploadedFile $file = null): void
    {
        $this->file = $file;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * Manages the copying of the file to the relevant place on the server
     */
    public function upload(): void
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

        // Determine subfolder based on context
        $mimeType = $this->getFile()->getClientMimeType();
        $subfolder = $this->context ?? (str_starts_with($mimeType, 'image/') ? 'photos' : 'rapports');
        $targetDir = self::SERVER_PATH_TO_FILES_FOLDER . '/' . $subfolder;

        // SECURITY: Use a completely random filename
        $fileName = bin2hex(random_bytes(16)) . '.' . $this->getFile()->guessExtension();

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true); // Changed from 0777 to 0755
        }

        $this->getFile()->move($targetDir, $fileName);
        
        $this->fileName = $fileName;
        $this->context = $subfolder;
        $this->mimeType = $this->getFile()->getClientMimeType();
        $this->fileSize = $this->getFile()->getSize();
        $this->originalFileName = $this->getFile()->getClientOriginalName();
        
        $this->setFile(null);
    }

    /**
     * Lifecycle callback to upload the file to the server.
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function lifecycleFileUpload(): void
    {
        $this->upload();
    }

    /**
     * Updates the hash value to force the preUpdate and postUpdate events to fire.
     */
    public function refreshUpdated(): void
    {
        $this->setUpdated(new \DateTime());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function __toString()
    {
        return $this->getFileWebPath();
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(?\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $extension): self
    {
        $this->mimeType = $extension;

        return $this;
    }

    #[Groups(["document.list", "document.details"])]
    public function getFileWebPath(): string
    {
        if (empty($this->fileName)) {
            return '#';
        }

        if ($this->context === null) {
            return '/uploads/' . $this->fileName;
        }

        return '/uploads/' . $this->context . '/' . $this->fileName;
    }

    public function getFileAbsolutePath()
    {
        if (empty($this->fileName)) {
            return null;
        }
        if ($this->context === null) {
            return self::SERVER_PATH_TO_FILES_FOLDER . '/' . $this->fileName;
        }
        return self::SERVER_PATH_TO_FILES_FOLDER . '/' . $this->context . '/' . $this->fileName;
    }

    public function getVisite(): ?Visite
    {
        return $this->visite;
    }

    public function setVisite(?Visite $visite): static
    {
        $this->visite = $visite;

        return $this;
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

    public function getFileSize(): ?string
    {
        return $this->fileSize;
    }

    public function setFileSize(?string $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getOriginalFileName(): ?string
    {
        return $this->originalFileName;
    }

    public function setOriginalFileName(?string $originalFileName): static
    {
        $this->originalFileName = $originalFileName;

        return $this;
    }
}
