<?php

namespace App\MessageHandler;

use App\Entity\Record;
use App\Message\Extract;
use App\Repository\RecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
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
        private HttpClientInterface             $httpClient,
        private CacheInterface                  $cache, // really only for testing, the pages are small and fast
    )
    {
    }

    public function __invoke(Extract $message): void
    {
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
            $id = $item['@admin']['sequence'];
            if (!$this->recordRepository->find($id)) {
                $record = new Record($id, $item);
                $this->entityManager->persist($record);
            }
        }
        $this->entityManager->flush();

        if ($data['stats']['remaining']) {
            $this->messageBus->dispatch(new Extract($url));
        }

    }
}
