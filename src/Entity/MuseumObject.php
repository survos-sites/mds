<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\GrpRepository;
use App\Repository\MuseumObjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Survos\MeiliBundle\Api\Filter\FacetsFieldSearchFilter;
use Survos\MeiliBundle\Metadata\MeiliIndex;
use Survos\WorkflowBundle\Traits\MarkingInterface;
use Survos\WorkflowBundle\Traits\MarkingTrait;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MuseumObjectRepository::class)]
#[ORM\Table(name: 'museum_object')]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ],
    normalizationContext: ['groups' => ['obj.read','marking', 'record.read']]
)]
#[MeiliIndex()]
//#[ApiFilter(OrderFilter::class, properties: [
//    'count','extractCount'
//])]
#[ApiFilter(FacetsFieldSearchFilter::class, properties: ['marking','material',  'status','license'])]
class MuseumObject implements MarkingInterface
{
    use MarkingTrait;

    public function __construct(
        #[ORM\Id]
        #[ApiProperty(identifier: true)]
        #[ORM\Column()]
        #[Groups(['record.read'])]
        private(set) ?string   $id = null,

        #[ORM\Column(type: Types::JSON, options: ['jsonb' => true])]
        #[Groups(['record.read'])]
        private(set) array  $rawData = [],

        #[ORM\Column(length: 255)]
        #[Groups(['record.read'])]
        private ?string $dataSource=null,

        #[ORM\Column()]
        #[Groups(['record.read'])]
        private ?string   $idWithinDataSource = null,
    )
    {
        assert($this->id === self::calcKey($this->dataSource, $this->idWithinDataSource));
        $this->marking = 'new';
        $this->dataSource = $dataSource;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    static public function calcKey(string $dataSource, string $id): string
    {
        return hash('xxh3', $dataSource .  $id);
    }

    #[Groups(['record.read'])]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    public array $data = []; // aka the map that was extracted.

    // --- Virtual properties using property hooks ---

    #[Groups(['record.read'])]
    public string $title {
        get => $this->data['Title'] ?? '';
    }


    #[Groups(['record.read'])]
    public string $description {
        get => $this->data['Brief Description'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $objectNumber {
        get => $this->data['Object Number'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $objectName {
        get => $this->data['Object Name'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $material {
        get => $this->data['Material'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $dimension {
        get => $this->data['Dimension'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $associatedPerson {
        get => $this->data['Associated Person'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $associatedDate {
        get => $this->data['Associated Date'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $productionDateStart {
        get => $this->data['Date - Earliest / Single'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $productionDateEnd {
        get => $this->data['Date - Latest'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $accessionDate {
        get => $this->data['Accession Date'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $acquisitionMethod {
        get => $this->data['Acquisition Method'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $concept {
        get => $this->data['Associated Concept'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $creditLine {
        get => $this->data['Credit Line'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $copyright {
        get => $this->data['Right Type'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $status {
        get => $this->data['Object Status'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $location {
        get => $this->data['Current Location'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $websiteLink {
        get => $this->data['Text Reference Number'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $license {
        get => $this->data['License'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $licenseUrl {
        get => $this->data['License Url'] ?? '';
    }

    #[Groups(['record.read'])]
    public string $year {
        get => $this->productionDateStart ?: $this->associatedDate;
    }

    public function getDataSource(): string
    {
        return $this->dataSource;
    }

    public function getRawData(): array
    {
        return $this->data;
    }
}
