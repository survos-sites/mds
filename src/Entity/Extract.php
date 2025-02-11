<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ExtractRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Survos\WorkflowBundle\Traits\MarkingInterface;
use Survos\WorkflowBundle\Traits\MarkingTrait;

#[ORM\Entity(repositoryClass: ExtractRepository::class)]
#[ApiResource]
class Extract implements MarkingInterface
{

    use MarkingTrait;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $token = null;

    /**
     * So we can index, use a code and the whole large token itself
     *
     * @var string|null
     */
    #[ORM\Column(length: 32)]
    #[ORM\Id]
    private ?string $tokenCode = null;

    #[ORM\Column(nullable: true, options: ['jsonb' => true])]
    private ?array $response = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $duration = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $nextToken = null;

    #[ORM\Column(nullable: true)]
    private ?int $latency = null;

    #[ORM\Column(nullable: true)]
    private ?int $errors = null;

    #[ORM\Column(nullable: true)]
    private ?int $remaining = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resume = null;


    /**
     * @param string|null $token
     */
    public function __construct(?string $token)
    {
        $this->token = $token;
        $this->createdAt = new \DateTimeImmutable();
        $this->tokenCode = self::calcCode($token);
    }

    static public function calcCode(string $token): string
    {
        return hash('xxh3', $token);
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getResponse(): ?array
    {
        return $this->response;
    }

    public function setResponse(?array $response): static
    {
        $this->response = $response;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getNextToken(): ?string
    {
        return $this->nextToken;
    }

    public function setNextToken(?string $nextToken): static
    {
        $this->nextToken = $nextToken;

        return $this;
    }

    public function getLatency(): ?int
    {
        return $this->latency;
    }

    public function setLatency(?int $latency): static
    {
        $this->latency = $latency;

        return $this;
    }

    public function getErrors(): ?int
    {
        return $this->errors;
    }

    public function setErrors(?int $errors): static
    {
        $this->errors = $errors;

        return $this;
    }

    public function getRemaining(): ?int
    {
        return $this->remaining;
    }

    public function setRemaining(?int $remaining): static
    {
        $this->remaining = $remaining;

        return $this;
    }

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function setResume(?string $resume): static
    {
        $this->resume = $resume;

        return $this;
    }

    public function getTokenCode(): ?string
    {
        return $this->tokenCode;
    }

    public function setTokenCode(string $tokenCode): static
    {
        $this->tokenCode = $tokenCode;

        return $this;
    }
}
