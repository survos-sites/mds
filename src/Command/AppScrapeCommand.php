<?php

namespace App\Command;

use App\Message\ExtractMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Survos\Scraper\Service\ScraperService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\RunsProcesses;

#[AsCommand('app:scrape', 'Scrape based on API key')]
final class AppScrapeCommand extends InvokableServiceCommand
{
    use RunsCommands;
    use RunsProcesses;


    public function __construct(
        protected EntityManagerInterface            $entityManager,
        protected LoggerInterface                                                  $logger,
        protected MessageBusInterface                                              $bus,
        protected CacheInterface                                                   $cache, // @todo: switch to PDO cache, $pdoCache
        protected ParameterBagInterface                                            $bag,
//        #[Target(OwnerWorkflowInterface::WORKFLOW_NAME)] private WorkflowInterface $ownerWorkflow,
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%env(MDS_API_KEY)%')] private string $apiKey,
        ?string                                                                     $name = null,
        private ?bool                                                              $reset = null,
//        protected                       $listingFilenames = [],
//        protected                       $detailFilenames = [],
    )
    {
        parent::__construct($name);
        $this->setHelp(sprintf(<<<EOL
Scrapes the bizarre 10-per-page data



EOL
        ));
    }



    public function __invoke(
        IO $io,
        #[Argument(description: "env api key if not defined")] ?string $apiKey=null,
    ): int {
        $apiKey=$apiKey??$this->apiKey;
        // kick off the search, sync so we can monitor

        $envelope = $this->bus->dispatch(new ExtractMessage($apiKey), [
            new TransportMessageIdStamp('sync')
        ]);

        return self::SUCCESS;    }
}
