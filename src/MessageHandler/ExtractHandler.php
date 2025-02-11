<?php

namespace App\MessageHandler;

use App\Entity\Extract;
use App\Entity\Record;
use App\Entity\Source;
use App\Message\ExtractMessage;
use App\Repository\ExtractRepository;
use App\Repository\RecordRepository;
use App\Repository\SourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Survos\CoreBundle\Service\SurvosUtils;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
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
        private ExtractRepository $extractRepository,
        private HttpClientInterface             $httpClient,
        private CacheInterface                  $cache, // really only for testing, the pages are small and fast
        private array $seen = []
    )
    {
    }

    public function __invoke(ExtractMessage $message): void
    {
        // we _could_ cache the page data the database if the process gets disrupted.
        // read the data and dispatch then next

        $token = $message->token;
//        dump($token); return;
        $url = self::BASE_URL . $token;
        $tokenCode = Extract::calcCode($token);
        if (!$extract = $this->extractRepository->findOneBy(['tokenCode' => $tokenCode])) {
            $extract = new Extract($token);
            $this->entityManager->persist($extract);
        }


        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Accept' => 'application/json',
            ]
        ]);
        if ($response->getStatusCode() !== 200) {
            dd($url);
        }
        $data = $response->toArray();
        $stats = $data['stats'];
        $duration = 1000* ($response->getInfo()['total_time']);
        $extract
            ->setResponse($data) // for debugging, but huge!  maybe for re-processing
            ->setDuration((int)$duration)
            ->setLatency($stats['latency']);
        ;
//        $this->entityManager->flush(); dd($stats);
        if (empty($limit)) {
            $limit = $data['stats']['total'] / $data['stats']['results'];
        }
        $url = $data['next_url'] ?? null;
        foreach ($data['data'] as $idx => $item) {
            // there can be multiple identifiers.  use the admin uuid if it exists
            $admin = $item['@admin'];
            $id = $admin['sequence'];

            SurvosUtils::assertKeyExists('data_source', $admin);
            $sourceData = $admin['data_source'];
            $sourceCode = $sourceData['code'];
            // flush after each...
//            if (!$source = $this->seen[$sourceCode]??false)
            {
                if (!$source = $this->sourceRepository->findOneBy(['code' => $sourceCode])) {
                    assert($sourceData['code'] == $sourceCode, $sourceData['code'] . "<> $sourceCode");
                    $source = new Source($sourceCode, $sourceData['name'], $sourceData['organisation'], $sourceData['group']);
                    $this->entityManager->persist($source);
                    $this->entityManager->flush();
                }
                $this->seen[$sourceCode] = $source;
            }

            // if it already exists, assume we also have the source
            if ($record = $this->recordRepository->find($id)) {
                assert($record->getSource() == $source);
                continue;
            }

            if (!$record = $this->recordRepository->find($id)) {
                $record = new Record($id, $item);
                $this->entityManager->persist($record);
            }

            $source->addRecord($record);
        }
        if ($data['has_next']) {
            $remaining =  $data['stats']['remaining'];
            $nextToken = $data['resume'];
            $extract
                ->setRemaining($remaining)
                ->setNextToken($nextToken);
        }
        $this->entityManager->flush();

        if ($remaining) {
            if ($data['has_next']) {
                $nextToken = $data['resume'];
                $extract
                    ->setRemaining($remaining)
                    ->setNextToken($nextToken);
                $extract->setNextToken($nextToken);

                if (!array_key_exists('resume', $data)) {
                    dd(data: $data, admin: $admin);
                }
                SurvosUtils::assertKeyExists('resume', $data);
                $this->messageBus->dispatch(new ExtractMessage($nextToken));

            }
        }


    }
}
