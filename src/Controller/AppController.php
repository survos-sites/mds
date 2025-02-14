<?php

namespace App\Controller;

use App\Entity\Extract;
use App\Entity\Record;
use App\Entity\Source;
use App\Repository\ExtractRepository;
use App\Repository\GrpRepository;
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
        private GrpRepository           $grpRepository,

    )
    {
    }
    #[Route('/record', name: 'app_record', methods: ['GET'])]
    #[Template('app/record.html.twig')]
    public function record(Request $request,
                           #[MapQueryParameter] int $limit = 100,
                           #[MapQueryParameter] ?string $source = null,
    ): Response|array
    {
        $filter = [];
        if ($source) {
            $filter['source'] = $source;
        }
        return [
            'columns' => [
            ],
            'data' => $this->recordRepository->findBy($filter, [], $limit),
        ];
    }

    #[Route('/source', name: 'app_source', methods: ['GET'])]
    #[Template('app/source.html.twig')]
    public function source(Request $request,
                            #[MapQueryParameter] int $limit = 10
    ): Response|array
    {
        return [
            'data' => $this->sourceRepository->findBy([], [], $limit),
        ];
    }

    #[Route('/grp', name: 'app_grp', methods: ['GET'])]
    #[Template('app/grp.html.twig')]
    public function grp(Request $request,
               #[MapQueryParameter] int $limit = 100,
    ): Response|array
    {
        $qb = $this->grpRepository->createQueryBuilder('g')
            ->select('SUM(g.count) as c');
        $total = ($qb->getQuery()->getSingleScalarResult());
        return [
            'total' => $total,
            'data' => $this->grpRepository->findBy([], ['name' => 'ASC  '], $limit),
        ];
    }

    #[Route('/extract', name: 'app_extract', methods: ['GET'])]
    #[Template('app/extract.html.twig')]
    public function extract(Request $request,
        #[MapQueryParameter] int $limit = 100,
        #[MapQueryParameter] ?string $grp=null,
    ): Response|array
    {
        $filter = [];
        if ($grp) {
            $filter['grp'] = $grp;
        }
        $lastExtract = $this->extractRepository->findBy($filter, ['createdAt' => 'DESC'], 9);

        $columns = [
            'tokenCode',
            'nextToken',
            'marking',
            'remaining',
            'age',
            'duration',
            'latency'
        ];
        if (!$grp) {
            $columns[] = 'grp';
        }
        return [
            'grp' => $grp,
            'columns' => $columns,
            'data' => $this->extractRepository->findBy($filter, ['createdAt' => 'DESC'], $limit),
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
