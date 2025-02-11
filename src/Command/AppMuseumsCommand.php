<?php

namespace App\Command;

use App\Entity\Source;
use App\Repository\SourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\RunsProcesses;

#[AsCommand('app:museums', 'Scrape the list of museums with objects')]
final class AppMuseumsCommand extends InvokableServiceCommand
{
    use RunsCommands;
    use RunsProcesses;

    public function __construct(
        private SourceRepository $sourceRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }
    public function __invoke(
        IO $io,
        #[Argument(description: 'html link')]
        string $url = 'https://museumdata.uk/explore-collections/?_sfm_has_object_records=1&_sf_ppp=100',

        #[Option(description: 'refresh the HTML page')]
        bool $refresh = true,
    ): int {
        $html = file_get_contents('data/museums.html');
        $crawler = new Crawler($html, $url);
        $crawler->filter('.museum-overview__content')->each(function (Crawler $node)  {
            $html = $node->html();
            if (preg_match('|https://museumdata.uk/museums/(q\d+)/|',$html, $matches )) {
                $sourceCode = strtoupper($matches[1]);
                if (!$source = $this->sourceRepository->findOneBy(['grp' => $sourceCode])) {
                    $source = new Source($sourceCode);
                }
                dd($matches);
            }
            dd($node->html());

        });
        $io->success($this->getName().' success.');

        return self::SUCCESS;
    }
}
