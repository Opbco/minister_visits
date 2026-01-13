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

    #[ORM\Column(length: 100, unique: true)]
    #[Groups(["document.list", "document.details", "depense.details", "depense.list", 'etablissement.details', 'user.details', 'classe.timetable'])]
    #[Assert\NotBlank(message: 'Le nom du fichier est obligatoire.')]
    private ?string $fileName = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updated = null;

    /**
     * Unmapped property to handle file uploads
     */
    private ?UploadedFile $file = null;

    #[ORM\Column(length: 100)]
    #[Groups(["document.list", "depense.details", "depense.list"])]
    private ?string $mimeType = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $context = null;

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
        $subfolder = $this->context ?? 'other';
        $targetDir = self::SERVER_PATH_TO_FILES_FOLDER . '/' . $subfolder;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // move takes the target directory and target filename as params
        $this->getFile()->move(
            $targetDir,
            $this->getFile()->getClientOriginalName()
        );

        // set the path property to the filename where you've saved the file
        $this->fileName = $this->getFile()->getClientOriginalName();
        $this->mimeType = $this->getFile()->getClientMimeType();

        // clean up the file property as you won't need it anymore
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

    #[Groups(["document.list", "document.details", 'bulletin:read', 'classe.timetable', 'user.details', 'paiement.list', 'depense.details', 'depense.list', 'salleclasse.student', 'etablissement.details'])]
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
}
