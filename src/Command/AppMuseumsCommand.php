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
            if (preg_match('|q=(.*?)&amp;show_all=1|', $html, $matches)) {
                $q = $matches[1];
                // get API key
//                https://museumdata.uk/get-api-token/get_api_token.php?user_id=4PZkaU6f9aRXejEE&institution=Museado&q=$q
            }
            if (preg_match('|https://museumdata.uk/museums/(q\d+)/|',$html, $matches )) {
                $sourceCode = strtoupper($matches[1]);
                if (!$source = $this->sourceRepository->findOneBy(['grp' => $sourceCode])) {
                    $source = new Source($sourceCode);
                }
            }
            $apiKey = 'kolXdB9v45--JPmMLvBvsO1WvP3nLAZF0UEGfrGqE6i-xayFbZL9htn5kbNgq/ba/YKA/pMtJPE8xGOhKMzBqLTUorG9zwUWjmpX7eIGGMT2CxPG8luPNbzO/kWi-eAwzY/aBG5ZD-QUPX8O6JZIYWu3L1KcP53N80MHmDapL9SaDzZDRBnPAc-ninTpjd2Y4jXJAplhltAAP3H-WMQf0-BYwohdUwvElJV5EytIewBz3j-idTiMOHUrL0jbZUGjXIwMVXQprJmE/C2s/ldSHUFKTqeV7/IAMXcqspxapNA=';
            $name = urldecode($q);
            $source
                ->setName($name);
            $source->setApiKey($apiKey);
            dd($matches, $html, );
            dd($html);
            dd($node->html());

        });
        $io->success($this->getName().' success.');

        return self::SUCCESS;
    }
}

