<?php

namespace App\Command;

use App\Entity\Grp;
use App\Entity\Source;
use App\Repository\GrpRepository;
use App\Repository\SourceRepository;
use App\Service\GrpExtractorService;
use App\Service\MuseumExtractor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsCommand('load:Grp', 'Scrape the html from https://museumdata.uk/explore-collections the Grp entity')]
final class LoadGrp extends Command
{

    public function __construct(
        private GrpRepository          $grpRepository,
        private EntityManagerInterface $entityManager,
        private CacheInterface $cache,
        private GrpExtractorService $grpExtractorService, // chatGPT
        private MuseumExtractor $museumExtractor, // co-pilot
        private array                  $grps=[],
    )
    {
        parent::__construct();
    }

    public function __invoke(
        SymfonyStyle     $io,
        #[Argument(description: 'html link')]
        string $url = 'https://museumdata.uk/explore-collections/?_sfm_has_object_records=1&_sf_ppp=100',

        #[Option(description: 'refresh the HTML page')]
        bool   $refresh = true,

        #[Option('max number to import')] ?int $max = null
    ): int
    {
        $filename = 'data/museums.html';
        if ($refresh) {
            file_put_contents($filename, file_get_contents($url));
        }
        $html = file_get_contents($filename);
//        $x = $this->museumExtractor->extract($html);
//        dd($x);
        $grps = $this->grpExtractorService->extract($html, $max);
        $this->entityManager->flush();
        $io->success("grp records loaded: " . count($grps));
        $io->writeln(<<<END

Next:


bin/console iterate Grp --stats
bin/console iterate Grp -m new -t get_api_key --limit 3
bin/consume
END
);

        return self::SUCCESS;
    }
}

