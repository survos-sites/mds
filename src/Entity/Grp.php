<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\GrpRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Survos\MeiliBundle\Api\Filter\FacetsFieldSearchFilter;
use Survos\MeiliBundle\Metadata\MeiliIndex;
use Survos\WorkflowBundle\Traits\MarkingInterface;
use Survos\WorkflowBundle\Traits\MarkingTrait;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: GrpRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ],
    normalizationContext: ['groups' => ['grp.read','marking']]
)]
#[MeiliIndex()]
#[ApiFilter(OrderFilter::class, properties: [
    'count','extractCount'
])]
#[ApiFilter(FacetsFieldSearchFilter::class, properties: ['marking', 'status','license'])]
class Grp implements MarkingInterface
{
    use MarkingTrait;

    public function __construct(
        #[ORM\Column(length: 255)]
        #[ORM\Id]
        #[Groups('grp.read')]
        private(set) string $id,

        #[ORM\Column(type: 'string', length: 255)]
        #[Groups('grp.read')]
        private(set) string $name {
            get => $this->name;
            set => $this->name = trim($value);
        },

        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private ?string $startToken = null
    )
    {
        $this->marking = 'new';
        $this->extracts = new ArrayCollection();
    }

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups('grp.read')]
    public ?string $wikidataId = null {
        get => $this->wikidataId;
        set => $this->wikidataId = $value ? strtoupper($value) : null;
    }

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups('grp.read')]
    public ?string $status = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $aliases = null {
        get => $this->aliases;
        set => $this->aliases = $value ? trim($value) : null;
    }

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups('grp.read')]
    public ?string $persistentLink = null {
        get => $this->persistentLink;
        set => $this->persistentLink = $value ? rtrim($value, '/') : null;
    }

    #[ORM\Column(type: 'boolean')]
    #[Groups('grp.read')]
    public bool $hasObjectRecords = false;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups('grp.read')]
    public ?string $description = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups('grp.read')]
    public ?string $licence = null {
        get => $this->licence;
        set => $this->licence = $value ? trim($value) : null;
    }

    /**
     * @var Collection<int, Extract>
     */
    #[ORM\OneToMany(targetEntity: Extract::class, mappedBy: 'grp', orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private Collection $extracts;

    #[ORM\Column(nullable: true)]
    #[Groups('grp.read')]
    private ?int $count = null;

    #[ORM\Column]
    #[Groups('grp.read')]
    private ?int $extractCount = 0;

    public function getStartToken(): ?string
    {
        return $this->startToken;
    }

    public function setStartToken(?string $startToken): static
    {
        $this->startToken = $startToken;

        return $this;
    }

    /**
     * @return Collection<int, Extract>
     */
    public function getExtracts(): Collection
    {
        return $this->extracts;
    }

    public function addExtract(Extract $extract): static
    {
        if (!$this->extracts->contains($extract)) {
            $this->extracts->add($extract);
            $extract->setGrp($this);
            $this->extractCount++;
        }

        return $this;
    }

    public function removeExtract(Extract $extract): static
    {
        if ($this->extracts->removeElement($extract)) {
            // set the owning side to null (unless already changed)
            if ($extract->getGrp() === $this) {
                $extract->setGrp(null);
            }
            $this->extractCount--;
        }

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(?int $count): static
    {
        $this->count = $count;

        return $this;
    }

    public function getExtractCount(): ?int
    {
        return $this->extractCount;
    }

    public function setExtractCount(int $extractCount): static
    {
        $this->extractCount = $extractCount;

        return $this;
    }

}
