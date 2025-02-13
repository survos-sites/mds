<?php

namespace App\Entity;

use App\Repository\GrpRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Survos\WorkflowBundle\Traits\MarkingInterface;
use Survos\WorkflowBundle\Traits\MarkingTrait;

#[ORM\Entity(repositoryClass: GrpRepository::class)]
class Grp implements MarkingInterface
{
    use MarkingTrait;

    public function __construct(
        #[ORM\Column(length: 255)]
        #[ORM\Id]
        private string $code,

        #[ORM\Column(length: 255)]
        private string $name,

        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private ?string $startToken = null
    )
    {
        $this->marking = 'new';
        $this->extracts = new ArrayCollection();

    }

    /**
     * @var Collection<int, Extract>
     */
    #[ORM\OneToMany(targetEntity: Extract::class, mappedBy: 'grp', orphanRemoval: true)]
    private Collection $extracts;

    #[ORM\Column(nullable: true)]
    private ?int $count = null;

    #[ORM\Column]
    private ?int $extractCount = 0;


    public function getId(): string
    {
        return $this->getCode();
    }

    public function getCode(): ?string
    {
        return $this->code;
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
