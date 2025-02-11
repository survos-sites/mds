<?php

namespace App\MessageHandler;

use App\Entity\Record;
use App\Entity\Source;
use App\Message\Extract;
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

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface    $messageBus,
        private readonly RecordRepository       $recordRepository,
        private readonly SourceRepository       $sourceRepository,
        private HttpClientInterface             $httpClient,
        private CacheInterface                  $cache, // really only for testing, the pages are small and fast
        private array $seen = []
    )
    {
    }

    public function __invoke(Extract $message): void
    {
        // we _could_ cache the page data the database if the process gets disrupted.
        // read the data and dispatch then next

        $url = $message->url;
        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Accept' => 'application/json',
            ]
        ]);
        if ($response->getStatusCode() !== 200) {
            dd($url);
        }
        $data = $response->toArray();
        if (empty($limit)) {
            $limit = $data['stats']['total'] / $data['stats']['results'];
        }
        $url = $data['next_url'] ?? null;
        foreach ($data['data'] as $idx => $item) {
            // there can be multiple identifiers.  use the admin uuid if it exists
            $admin = $item['@admin'];
            $id = $admin['sequence'];
            // if it already exists, assume we also have the source
            if ($this->recordRepository->find($id)) {
                continue;
            }

            if (!$record = $this->recordRepository->find($id)) {
                $record = new Record($id, $item);
                $this->entityManager->persist($record);
            }
            SurvosUtils::assertKeyExists('data_source', $admin);
            $sourceData = $admin['data_source'];
            $code = $sourceData['code'];
            if (!$source = $this->seen[$code]??false) {
                if (!$source = $this->sourceRepository->findOneBy(['code' => $code])) {
                    $source = new Source(...array_values($sourceData));
                    $this->entityManager->persist($source);
                    $this->seen[$code] = $source;
                }
            }
            $source->addRecord($record);
        }
        $this->entityManager->flush();

        if ($data['stats']['remaining']) {
            $this->messageBus->dispatch(new Extract($url));
        }

    }
}
