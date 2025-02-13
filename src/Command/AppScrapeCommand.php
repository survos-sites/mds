<?php

namespace App\Command;

use App\Entity\Extract;
use App\Message\ExtractMessage;
use App\Repository\ExtractRepository;
use App\Repository\GrpRepository;
use App\Repository\SourceRepository;
use App\Workflow\ExtractWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Survos\WorkflowBundle\Message\AsyncTransitionMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\RunsProcesses;

#[AsCommand('app:scrape', 'Scrape a grp based on API key')]
final class AppScrapeCommand extends InvokableServiceCommand
{
    use RunsCommands;
    use RunsProcesses;


    public function __construct(
        protected EntityManagerInterface                 $entityManager,
        protected LoggerInterface                        $logger,
        protected MessageBusInterface                    $bus,
        protected CacheInterface                         $cache, // @todo: switch to PDO cache, $pdoCache
        protected ParameterBagInterface                  $bag,
        private MessageBusInterface                      $messageBus,
        private GrpRepository                            $grpRepository,
        private ExtractRepository                        $extractRepository,
//        #[Target(OwnerWorkflowInterface::WORKFLOW_NAME)] private WorkflowInterface $ownerWorkflow,
        private readonly HttpClientInterface             $httpClient,

        #[Autowire('%env(MDS_API_KEY)%')] private string $apiKey,
        ?string                                          $name = null,
        private ?bool                                    $reset = null,
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
        IO                                                      $io,
        #[Argument(description: "code for grp")] ?string        $gprCode,
        #[Option(description: "dispatch a fetch request")] bool $dispatch = false,
        #[Option(description: "dispatch all groups")] bool      $all = false,
    ): int
    {
        if ($all) {
            $grps = $this->grpRepository->findAll();
        } else {
            $grps = [$this->grpRepository->find($gprCode)];
        }
        assert(count($grps), "invalid code: $gprCode");
        foreach ($grps as $grp) {

            $token = $grp->getStartToken();
            $tokenCode = Extract::calcCode($token);

            if (!$extract = $this->extractRepository->findOneBy(['tokenCode' => $tokenCode])) {
                $extract = new Extract($token, $grp);
                assert($extract->getTokenCode() === $tokenCode);
                $this->entityManager->persist($extract);
            }

            $this->entityManager->flush();
            // start the search process with
            //  ./c workflow:iterate App\\Entity\\Extract --marking=new --transition=fetch -vvv
            // or dispatch a TransitionMessage
            // kick off the search, sync so we can monitor
            if ($dispatch) {
                $envelope = $this->messageBus->dispatch(new AsyncTransitionMessage(
                    $extract->getTokenCode(),
                    Extract::class,
                    ExtractWorkflow::TRANSITION_FETCH,
                    ExtractWorkflow::WORKFLOW_NAME,
                ), [
//                new TransportNamesStamp('sync')
                ]);
                $io->success("Extract dispatched for " . $grp->getName());

            }
        }
        $io->success("Extract created/updated");


        return self::SUCCESS;
    }
}
