<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\SourceRepository;
use App\Workflow\SourceWorkflowInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Survos\WorkflowBundle\Traits\MarkingInterface;
use Survos\WorkflowBundle\Traits\MarkingTrait;

#[ORM\Entity(repositoryClass: SourceRepository::class)]
#[ORM\UniqueConstraint(name: 'source_code', columns: ['code'])]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ]
)]

class Source implements MarkingInterface, SourceWorkflowInterface
{
    use MarkingTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Record>
     */
    #[ORM\OneToMany(targetEntity: Record::class, mappedBy: 'source', orphanRemoval: true, cascade: ['persist'])]
    private Collection $records;

    #[ORM\Column(nullable: true)]
    private ?int $recordCount = null;

    // the starting API key
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $apiKey = null;

    #[ORM\Column(nullable: true)]
    private ?int $expectedCount = null;

    /**
     * What a pain -- we don't have the code until after we've fetched the item.  So we need nesting or two entities.
     *
     */
    public function __construct(
        #[ORM\Column(length: 255, unique: true)]
        private ?string $code = null,

        #[ORM\Column(length: 255)]
        private ?string $name = null,

        #[ORM\Column(length: 255, nullable: true)]
        private ?string $org = null,

        #[ORM\Column(length: 255, nullable: true)]
        private ?string $grp = null,
    )
    {
        $this->records = new ArrayCollection();
        $this->recordCount = 0;
        $this->marking = self::PLACE_NEW;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getOrg(): ?string
    {
        return $this->org;
    }

    public function setOrg(?string $org): static
    {
        $this->org = $org;

        return $this;
    }

    public function getGrp(): ?string
    {
        return $this->grp;
    }

    public function setGrp(?string $grp): static
    {
        $this->grp = $grp;

        return $this;
    }

    /**
     * @return Collection<int, Record>
     */
    public function getRecords(): Collection
    {
        return $this->records;
    }

    public function addRecord(Record $record): static
    {
        if (!$this->records->contains($record)) {
            $this->records->add($record);
            $record->setSource($this);
            $this->recordCount++;
        }

        return $this;
    }

    public function removeRecord(Record $record): static
    {
        if ($this->records->removeElement($record)) {
            // set the owning side to null (unless already changed)
            if ($record->getSource() === $this) {
                $record->setSource(null);
                $this->recordCount--;
            }
        }

        return $this;
    }

    public function getRecordCount(): ?int
    {
        return $this->recordCount;
    }

    public function setRecordCount(?int $recordCount): static
    {
        $this->recordCount = $recordCount;

        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }


    public function getExpectedCount(): ?int
    {
        return $this->expectedCount;
    }

    public function setExpectedCount(int $expectedCount): static
    {
        $this->expectedCount = $expectedCount;

        return $this;
    }
}
