<?php

namespace App\Service;

use App\Entity\MuseumObject;
use App\Repository\MuseumObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

class MuseumObjectExtractor
{

    public function __construct(
        private MuseumObjectRepository $museumObjectRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @param array $jsonData
     * @return MuseumObject[]
     */
    public function extract(array $jsonData): array
    {
        $objects = [];

        foreach ($jsonData['data'] as $item) {
            $document = $item['@document']['units'] ?? [];
            $admin = $item['@admin'] ?? [];

            $map = $this->flattenUnits($document);
            $idWithinDataSource = $admin['id'] ?? null;
            assert($idWithinDataSource !== null);
            $map['id'] = $idWithinDataSource;

            $dataSource = $admin['data_source']['name'] ?? 'Unknown';
            $id = MuseumObject::calcKey($dataSource, $idWithinDataSource);
            if (!$obj = $this->museumObjectRepository->find($id)) {
                $obj = new MuseumObject($id, $item, $dataSource, $idWithinDataSource);
                $this->entityManager->persist($obj);
            }
            $obj->data = $map;
//            $obj = new MuseumObject($map, $dataSource);
//            dd($obj->title, (array)$obj);
            $objects[] = $obj;
        }
        $this->entityManager->flush();

        return $objects;
    }

    private function flattenUnits(array $units): array
    {
        $map = [];
        foreach ($units as $unit) {
            if (isset($unit['label'], $unit['value'])) {
                $map[$unit['label']] = $unit['value'];
            }
            if (isset($unit['units'])) {
                $map = array_merge($map, $this->flattenUnits($unit['units']));
            }
        }
        return $map;
    }
}
