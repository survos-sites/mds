<?php

namespace App\Entity;

use App\Repository\RecordRepository;
use App\Workflow\RecordWorkflowInterface;
use Doctrine\ORM\Mapping as ORM;
use Survos\WorkflowBundle\Traits\MarkingInterface;
use Survos\WorkflowBundle\Traits\MarkingTrait;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: RecordRepository::class)]
class Record implements MarkingInterface, RecordWorkflowInterface, \Stringable
{
    use MarkingTrait;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME)]
        private Uuid $id,

        #[ORM\ManyToOne(inversedBy: 'records',cascade: ['persist', 'remove'])]
        #[ORM\JoinColumn(nullable: false)]
        private Source $source,

        #[ORM\Column(options: ['jsonb' => true])]
        private array $data,

        #[ORM\ManyToOne(inversedBy: 'records', cascade: ['persist'])]
        #[ORM\JoinColumn(referencedColumnName: 'token_code', nullable: false)]
        private Extract $extract


    ) {
        $this->marking = self::PLACE_NEW;
        $this->extract->addRecord($this);
        $this->source->addRecord($this);
    }

    public function getId(): Uuid
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
        return $this->getId();
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
