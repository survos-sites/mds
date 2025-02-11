<?php

namespace App\Controller;

use App\Entity\Extract;
use App\Entity\Record;
use App\Entity\Source;
use App\Repository\ExtractRepository;
use App\Repository\RecordRepository;
use App\Repository\SourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ExtractRepository      $extractRepository,
        private RecordRepository       $recordRepository,
        private SourceRepository       $sourceRepository,

    )
    {
    }
    #[Route('/record', name: 'app_record', methods: ['GET'])]
    #[Template('app/source.html.twig')]
    public function record(Request $request,
                           #[MapQueryParameter] int $limit = 10
    ): Response|array
    {
        return [
            'columns' => [
                'id',
                'marking',
                'source',
            ],
            'data' => $this->recordRepository->findBy([], [], $limit),
        ];
    }

    #[Route('/source', name: 'app_source', methods: ['GET'])]
    #[Template('app/source.html.twig')]
    public function source(Request $request,
                            #[MapQueryParameter] int $limit = 10
    ): Response|array
    {
        return [
            'columns' => [
                'name',
                'grp',
                'org',
                'code',
            ],
            'data' => $this->sourceRepository->findBy([], [], $limit),
        ];
    }

    #[Route('/extract', name: 'app_extract', methods: ['GET'])]
    #[Template('app/extract.html.twig')]
    public function extract(Request $request,
        #[MapQueryParameter] int $limit = 10
    ): Response|array
    {
        $lastExtract = $this->extractRepository->findBy([], ['createdAt' => 'DESC'], 9);

        return [
            'columns' => [                'tokenCode',
                'nextToken',
                'remaining',
                'duration',
                'latency'
            ],
            'data' => $this->extractRepository->findBy([], ['createdAt' => 'DESC'], $limit),
            'lastExtract' => $lastExtract,
        ];
    }

    #[Route('/', name: 'app_homepage')]
    public function index(
    ): Response
    {
        foreach ([Extract::class, Record::class, Source::class] as $class) {
            $repo = $this->entityManager->getRepository($class);
            $counts[$class] = $repo->count();

//            $data = $repo->findBy([], ['createdAt' => 'DESC'], 10);
        }
        return $this->render('app/index.html.twig', [
            'counts' => $counts,
        ]);
    }
}
