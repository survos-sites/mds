<?php

namespace App\Entity;

use App\Repository\SourceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SourceRepository::class)]
#[ORM\UniqueConstraint(name: 'source_code', columns: ['code'])]
class Source
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Record>
     */
    #[ORM\OneToMany(targetEntity: Record::class, mappedBy: 'source', orphanRemoval: true)]
    private Collection $records;

    /**
     * @param int|null $id
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
        }

        return $this;
    }

    public function removeRecord(Record $record): static
    {
        if ($this->records->removeElement($record)) {
            // set the owning side to null (unless already changed)
            if ($record->getSource() === $this) {
                $record->setSource(null);
            }
        }

        return $this;
    }
}
