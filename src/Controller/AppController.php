<?php

namespace App\Controller;

use App\Entity\Extract;
use App\Entity\Record;
use App\Entity\Source;
use App\Repository\ExtractRepository;
use App\Repository\RecordRepository;
use App\Repository\SourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/', name: 'app_homepage')]
    public function index(
        ExtractRepository $extractRepository,
        RecordRepository $recordRepository,
        SourceRepository $sourceRepository,
    ): Response
    {
        $columnsByClass = [
            Extract::class => [
                'tokenCode',
                'nextToken',
                'duration',
                'latency'
            ],
            Record::class => [
                'id',
                'marking',
                'source',
            ],
            Source::class => [
                'code',
                'recordCount',
                'name',
                'org',
                'grp'
            ]
        ];

        $lastExtract = $extractRepository->findBy([], ['createdAt' => 'DESC'], 2);
        foreach ([Record::class, Source::class, Extract::class] as $class) {
            $repo = $this->entityManager->getRepository($class);
            $counts[$class] = $repo->count();
            $data[$class] = $repo->findBy([], [], 10);

//            $data = $repo->findBy([], ['createdAt' => 'DESC'], 10);
        }
        return $this->render('app/index.html.twig', [
            'counts' => $counts,
            'lastExtract' => $lastExtract,
            'columnsByClass' => $columnsByClass,
            'data' => $data,
        ]);
    }
}
