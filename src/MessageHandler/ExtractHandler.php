<?php

namespace App\MessageHandler;

use App\Entity\Extract;
use App\Entity\Record;
use App\Entity\Source;
use App\Message\ExtractMessage;
use App\Repository\ExtractRepository;
use App\Repository\GrpRepository;
use App\Repository\RecordRepository;
use App\Repository\SourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Survos\CoreBundle\Service\SurvosUtils;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


#[AsMessageHandler]
final class ExtractHandler
{
    const BASE_URL = 'https://mds-data-1.ciim.k-int.com/api/v1/extract?resume=';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface    $messageBus,
        private readonly RecordRepository       $recordRepository,
        private readonly SourceRepository       $sourceRepository,
        private readonly GrpRepository       $grpRepository,
        private ExtractRepository               $extractRepository,
        private HttpClientInterface             $httpClient,
        private LoggerInterface $logger,
        private array                           $seen = []
    )
    {
    }

    public function __invoke(ExtractMessage $message): void
    {
        // we _could_ cache the page data the database if the process gets disrupted.
        // read the data and dispatch then next
        $grp = $this->grpRepository->find($message->grpCode);
        if (!$grp) {
            assert($grp, "Missing " . $message->grpCode);
            return;
        }

        if (!$extract = $this->extractRepository->findByTokenCode($message->tokenCode)) {
            assert($extract, "Missing " . $message->tokenCode);
            return;
        }

        foreach ($message->data['data'] as $idx => $item) {
            // there can be multiple identifiers.  use the admin uuid if it exists
            assert(array_key_exists('@admin', $item), "no @admin in #$idx of $message->tokenCode");
            $admin = $item['@admin'];
            $id = $admin['uuid'];

            SurvosUtils::assertKeyExists('data_source', $admin);
            $sourceData = $admin['data_source'];
            $sourceCode = $sourceData['code'];
            // flush after each...
            // this is the REAL source, not the grp
            if (array_key_exists($sourceCode, $this->seen)) {
                $source = $this->seen[$sourceCode];
            } elseif (!$source = $this->sourceRepository->findOneBy(['code' => $sourceCode])) {
                assert($sourceData['code'] == $sourceCode, $sourceData['code'] . "<> $sourceCode");
                // @todo: add the group here.
                $source = new Source($sourceCode, $sourceData['name'], $sourceData['organisation'], $sourceData['group']);
                $this->entityManager->persist($source);
                $this->entityManager->flush();
                $this->seen[$sourceCode] = $source;
            }

            // if it already exists, assume we also have the source
            $uuid = new Uuid($id);
            if ($record = $this->recordRepository->find($uuid)) {
                assert($record->getSource() == $source);
                continue;
            } else {
                $record = new Record($uuid, $source, $item, $extract);
                $this->entityManager->persist($record);
                $this->entityManager->flush();
            }
        }
        $this->entityManager->flush();



    }
}
