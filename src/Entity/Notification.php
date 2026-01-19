<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\NotificationRepository;
use App\Enum\NotificationType;
use App\Enum\NotificationStatus;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notification')]
#[ORM\Index(columns: ['status'], name: 'idx_notification_status')]
#[ORM\Index(columns: ['type'], name: 'idx_notification_type')]
#[ORM\Index(columns: ['sent_at'], name: 'idx_notification_sent_at')]
#[ORM\Index(columns: ['reunion_id'], name: 'idx_notification_reunion')]
#[ORM\Index(columns: ['personnel_id'], name: 'idx_notification_personnel')]
#[ORM\Index(columns: ['external_participant_id'], name: 'idx_notification_external')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['notification:read']],
    denormalizationContext: ['groups' => ['notification:write']],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
    ],
    order: ['createdAt' => 'DESC']
)]
#[ApiFilter(SearchFilter::class, properties: [
    'reunion' => 'exact',
    'personnel' => 'exact',
    'externalParticipant' => 'exact',
    'status' => 'exact',
    'type' => 'exact'
])]
#[ApiFilter(DateFilter::class, properties: ['sentAt', 'readAt', 'createdAt'])]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['notification:read', 'reunion:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "The meeting is required.")]
    #[Groups(['notification:read', 'notification:write'])]
    private ?Reunion $reunion = null;

    // --- RECIPIENT: Internal Personnel OR External Participant ---
    #[ORM\ManyToOne]
    #[Groups(['notification:read', 'notification:write'])]
    private ?Personnel $personnel = null;

    #[ORM\ManyToOne]
    #[Groups(['notification:read', 'notification:write'])]
    private ?ExternalParticipant $externalParticipant = null;

    // --- NOTIFICATION DETAILS ---
    #[ORM\Column(type: Types::STRING, length: 20, enumType: NotificationType::class)]
    #[Assert\NotNull(message: "Notification type is required.")]
    #[Groups(['notification:read', 'notification:write'])]
    private ?NotificationType $type = null;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: NotificationStatus::class)]
    #[Groups(['notification:read', 'notification:write'])]
    private ?NotificationStatus $status = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['notification:read', 'notification:write'])]
    private ?string $subject = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['notification:read', 'notification:write'])]
    private ?string $message = null;

    // --- CONTACT INFO (automatically populated from Personnel or ExternalParticipant) ---
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['notification:read'])]
    private ?string $recipientEmail = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['notification:read'])]
    private ?string $recipientPhone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['notification:read'])]
    private ?string $recipientName = null;

    // --- TIMESTAMPS ---
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['notification:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['notification:read'])]
    private ?\DateTimeInterface $sentAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['notification:read', 'notification:write'])]
    private ?\DateTimeInterface $readAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['notification:read'])]
    private ?\DateTimeInterface $deliveredAt = null;

    // --- ERROR TRACKING ---
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['notification:read'])]
    private ?string $errorMessage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['notification:read'])]
    private ?int $retryCount = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    #[Groups(['notification:read'])]
    private ?\DateTimeInterface $lastRetryAt = null;

    // --- METADATA ---
    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['notification:read', 'notification:write'])]
    private ?array $metadata = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['notification:read', 'notification:write'])]
    private ?User $user_created = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = NotificationStatus::PENDING;
        $this->retryCount = 0;
    }

    // --- Validation to ensure consistency ---
    #[Assert\Callback]
    public function validateRecipient(ExecutionContextInterface $context, $payload): void
    {
        if (null === $this->personnel && null === $this->externalParticipant) {
            $context->buildViolation('You must select either a Personnel or an External Participant.')
                ->atPath('personnel')
                ->addViolation();
        }

        if (null !== $this->personnel && null !== $this->externalParticipant) {
            $context->buildViolation('A recipient cannot be both Personnel and External Participant.')
                ->atPath('personnel')
                ->addViolation();
        }

        // Validate that contact info is available for the notification type
        if ($this->type === NotificationType::EMAIL && empty($this->getRecipientEmail())) {
            $context->buildViolation('Email address is required for email notifications.')
                ->atPath('type')
                ->addViolation();
        }

        if ($this->type === NotificationType::SMS && empty($this->getRecipientPhone())) {
            $context->buildViolation('Phone number is required for SMS notifications.')
                ->atPath('type')
                ->addViolation();
        }
    }

    // ==================== GETTERS / SETTERS ====================

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

    public function getPersonnel(): ?Personnel
    {
        return $this->personnel;
    }

    public function setPersonnel(?Personnel $personnel): static
    {
        $this->personnel = $personnel;
        
        // Clear external participant when setting personnel
        if ($personnel !== null) {
            $this->externalParticipant = null;
            
            // Auto-populate contact information
            $this->recipientName = $personnel->getNomComplet();
            $this->recipientEmail = $personnel->getEmail();
            $this->recipientPhone = $personnel->getTelephone();
        }
        
        return $this;
    }

    public function getExternalParticipant(): ?ExternalParticipant
    {
        return $this->externalParticipant;
    }

    public function setExternalParticipant(?ExternalParticipant $externalParticipant): static
    {
        $this->externalParticipant = $externalParticipant;
        
        // Clear personnel when setting external participant
        if ($externalParticipant !== null) {
            $this->personnel = null;
            
            // Auto-populate contact information
            $this->recipientName = $externalParticipant->getNom();
            $this->recipientEmail = $externalParticipant->getEmail();
            $this->recipientPhone = $externalParticipant->getTelephone();
        }
        
        return $this;
    }

    public function getRecipientEmail(): ?string
    {
        // Return stored value or get from related entity
        if ($this->recipientEmail) {
            return $this->recipientEmail;
        }
        
        if ($this->personnel) {
            return $this->personnel->getEmail();
        }
        
        if ($this->externalParticipant) {
            return $this->externalParticipant->getEmail();
        }
        
        return null;
    }

    public function setRecipientEmail(?string $recipientEmail): static
    {
        $this->recipientEmail = $recipientEmail;
        return $this;
    }

    public function getRecipientPhone(): ?string
    {
        // Return stored value or get from related entity
        if ($this->recipientPhone) {
            return $this->recipientPhone;
        }
        
        if ($this->personnel) {
            return $this->personnel->getTelephone();
        }
        
        if ($this->externalParticipant) {
            return $this->externalParticipant->getTelephone();
        }
        
        return null;
    }

    public function setRecipientPhone(?string $recipientPhone): static
    {
        $this->recipientPhone = $recipientPhone;
        return $this;
    }

    public function getRecipientName(): ?string
    {
        // Return stored value or get from related entity
        if ($this->recipientName) {
            return $this->recipientName;
        }
        
        if ($this->personnel) {
            return $this->personnel->getNomComplet();
        }
        
        if ($this->externalParticipant) {
            return $this->externalParticipant->getNom();
        }
        
        return 'Unknown';
    }

    public function setRecipientName(?string $recipientName): static
    {
        $this->recipientName = $recipientName;
        return $this;
    }

    public function getType(): ?NotificationType
    {
        return $this->type;
    }

    public function setType(NotificationType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getStatus(): ?NotificationStatus
    {
        return $this->status;
    }

    public function setStatus(NotificationStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeInterface $sentAt): static
    {
        $this->sentAt = $sentAt;
        return $this;
    }

    public function getReadAt(): ?\DateTimeInterface
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTimeInterface $readAt): static
    {
        $this->readAt = $readAt;
        return $this;
    }

    public function getDeliveredAt(): ?\DateTimeInterface
    {
        return $this->deliveredAt;
    }

    public function setDeliveredAt(?\DateTimeInterface $deliveredAt): static
    {
        $this->deliveredAt = $deliveredAt;
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): static
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    public function getRetryCount(): ?int
    {
        return $this->retryCount;
    }

    public function setRetryCount(int $retryCount): static
    {
        $this->retryCount = $retryCount;
        return $this;
    }

    public function getLastRetryAt(): ?\DateTimeInterface
    {
        return $this->lastRetryAt;
    }

    public function setLastRetryAt(?\DateTimeInterface $lastRetryAt): static
    {
        $this->lastRetryAt = $lastRetryAt;
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): static
    {
        $this->metadata = $metadata;
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

    // ==================== HELPER METHODS ====================

    /**
     * Get recipient display name (same pattern as ReunionParticipation::getParticipantName())
     */
    public function getRecipientDisplayName(): string
    {
        if ($this->personnel) {
            return (string) $this->personnel;
        }
        if ($this->externalParticipant) {
            return sprintf('%s (%s)', 
                $this->externalParticipant->getNom(), 
                $this->externalParticipant->getOrganisation()
            );
        }
        return $this->recipientName ?? 'Unknown';
    }

    /**
     * Check if recipient is internal staff
     */
    public function isInternalRecipient(): bool
    {
        return $this->personnel !== null;
    }

    /**
     * Check if recipient is external participant
     */
    public function isExternalRecipient(): bool
    {
        return $this->externalParticipant !== null;
    }

    // ==================== BUSINESS METHODS ====================

    /**
     * Mark notification as sent
     */
    public function markAsSent(): static
    {
        $this->status = NotificationStatus::SENT;
        $this->sentAt = new \DateTime();
        return $this;
    }

    /**
     * Mark notification as delivered
     */
    public function markAsDelivered(): static
    {
        $this->status = NotificationStatus::DELIVERED;
        $this->deliveredAt = new \DateTime();
        return $this;
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): static
    {
        $this->status = NotificationStatus::READ;
        if (!$this->readAt) {
            $this->readAt = new \DateTime();
        }
        return $this;
    }

    /**
     * Mark notification as failed
     */
    public function markAsFailed(string $errorMessage): static
    {
        $this->status = NotificationStatus::FAILED;
        $this->errorMessage = $errorMessage;
        return $this;
    }

    /**
     * Increment retry count
     */
    public function incrementRetry(): static
    {
        $this->retryCount++;
        $this->lastRetryAt = new \DateTime();
        return $this;
    }

    /**
     * Check if notification can be retried
     */
    public function canRetry(int $maxRetries = 3): bool
    {
        return $this->status === NotificationStatus::FAILED && $this->retryCount < $maxRetries;
    }

    /**
     * Check if notification is pending
     */
    public function isPending(): bool
    {
        return $this->status === NotificationStatus::PENDING;
    }

    /**
     * Check if notification was successfully sent
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, [
            NotificationStatus::SENT,
            NotificationStatus::DELIVERED,
            NotificationStatus::READ
        ]);
    }

    /**
     * Get recipient contact based on notification type
     */
    public function getRecipientContact(): ?string
    {
        return match($this->type) {
            NotificationType::EMAIL => $this->getRecipientEmail(),
            NotificationType::SMS, NotificationType::WHATSAPP => $this->getRecipientPhone(),
            NotificationType::PUSH, NotificationType::IN_APP => 
                $this->personnel?->getId() ? (string)$this->personnel->getId() : null,
            default => null,
        };
    }

    /**
     * Validate that recipient information is complete for the notification type
     */
    public function hasValidRecipient(): bool
    {
        return match($this->type) {
            NotificationType::EMAIL => !empty($this->getRecipientEmail()),
            NotificationType::SMS, NotificationType::WHATSAPP => !empty($this->getRecipientPhone()),
            NotificationType::PUSH, NotificationType::IN_APP => $this->personnel !== null,
            default => false,
        };
    }

    // ==================== LIFECYCLE CALLBACKS ====================

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
        if ($this->status === null) {
            $this->status = NotificationStatus::PENDING;
        }
        if ($this->retryCount === null) {
            $this->retryCount = 0;
        }
        
        // Auto-populate recipient info from personnel or external participant
        if ($this->personnel && !$this->recipientName) {
            $this->recipientName = $this->personnel->getNomComplet();
            $this->recipientEmail = $this->personnel->getEmail();
            $this->recipientPhone = $this->personnel->getTelephone();
        } elseif ($this->externalParticipant && !$this->recipientName) {
            $this->recipientName = $this->externalParticipant->getNom();
            $this->recipientEmail = $this->externalParticipant->getEmail();
            $this->recipientPhone = $this->externalParticipant->getTelephone();
        }
    }

    public function __toString(): string
    {
        return sprintf(
            '%s notification to %s',
            $this->type?->value ?? 'Unknown',
            $this->getRecipientDisplayName()
        );
    }
}