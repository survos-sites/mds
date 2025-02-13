<?php

namespace App\Command;

use App\Entity\Grp;
use App\Entity\Source;
use App\Repository\GrpRepository;
use App\Repository\SourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\RunsProcesses;

#[AsCommand('app:museums', 'Scrape the list of museums into Grp')]
final class AppMuseumsCommand extends InvokableServiceCommand
{
    use RunsCommands;
    use RunsProcesses;

    public function __construct(
        private GrpRepository          $grpRepository,
        private EntityManagerInterface $entityManager,
        private CacheInterface $cache,
        private array                  $grps=[],
    )
    {
        parent::__construct();
    }

    public function __invoke(
        IO     $io,
        #[Argument(description: 'html link')]
        string $url = 'https://museumdata.uk/explore-collections/?_sfm_has_object_records=1&_sf_ppp=100',

        #[Option(description: 'refresh the HTML page')]
        bool   $refresh = true,
    ): int
    {
        $html = file_get_contents('data/museums.html');
        $crawler = new Crawler($html, $url);
        $crawler->filter('.museum-overview__content')->each(function (Crawler $node) {
            if (count($this->grps) > 3) {
//                return;
            }

            $html = $node->html();
            if (preg_match('|https://museumdata.uk/museums/(q\d+)/|', $html, $matches)) {
                $grpCode = strtoupper($matches[1]);

                if (preg_match('|q=(.*?)&amp;show_all=1|', $html, $matches)) {
                    $q = $matches[1];
                    $name = urldecode($q);
                    // get API key
                }


                if (!$grp = $this->grpRepository->find($grpCode)) {
                    $grp = new Grp($grpCode, $name);
                    $this->entityManager->persist($grp);
                }
                if (!$grp->getStartToken()) {
                    $apiKeyRequest = "https://museumdata.uk/get-api-token/get_api_token.php?user_id=tacman&institution=Museado&q=$q";
                    $apiKeyData  = $this->cache->get($grpCode, fn(ItemInterface $item) => json_decode(file_get_contents($apiKeyRequest)));
                    $grp
                        ->setCount($apiKeyData->count)
                        ->setStartToken($apiKeyData->resume);
                }

//                $grp->setApiKey($apiKey);
                $this->entityManager->flush();
                $this->grps[] = $grp;
            }
        });
        $this->entityManager->flush();
        $io->success($this->getName() . ' success.');

        foreach ($this->grps as $grp) {
            $io->success($grp->getName());
        }

        return self::SUCCESS;
    }
}

