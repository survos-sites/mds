<?php

namespace App\Entity;

use App\Repository\RecordRepository;
use App\Workflow\RecordWorkflowInterface;
use Doctrine\ORM\Mapping as ORM;
use Survos\WorkflowBundle\Traits\MarkingInterface;
use Survos\WorkflowBundle\Traits\MarkingTrait;

#[ORM\Entity(repositoryClass: RecordRepository::class)]
class Record implements MarkingInterface, RecordWorkflowInterface, \Stringable
{
    use MarkingTrait;
    #[ORM\ManyToOne(inversedBy: 'records',cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Source $source = null;

    #[ORM\ManyToOne(inversedBy: 'records')]
    #[ORM\JoinColumn(referencedColumnName: 'tokenCode', nullable: false)]
    private ?Extract $extract = null;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        private ?int  $id = null,

        #[ORM\Column(options: ['jsonb' => true])]
        private array $data = [],
    ) {
        $this->marking = self::PLACE_NEW;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getSource(): ?Source
    {
        return $this->source;
    }

    public function setSource(?Source $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }

    public function getExtract(): ?Extract
    {
        return $this->extract;
    }

    public function setExtract(?Extract $extract): static
    {
        $this->extract = $extract;

        return $this;
    }
}
