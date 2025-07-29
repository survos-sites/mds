<?php

namespace App\Service;

use App\Entity\Grp;
use App\Repository\GrpRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class GrpExtractorService
{
    public function __construct(
        private readonly GrpRepository $grpRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger
    ) {
    }
	/**
	 * @return Grp[]
	 */
	public function extract(string $html, ?int $max): array
	{
		$crawler = new Crawler($html);
		$groups = [];

		$crawler->filter('details.museum-overview')->each(function (Crawler $museumNode) use (&$groups, $max) {
		    // Museum name

            if ($max && (count($groups) >= $max)) {
                return;
            }

            $name = $museumNode->filter('.museum-overview__heading')->text('');
            $this->logger->warning("Found $name");

		    // Persistent link and internal code
		    $plink = $museumNode->filter('dt:contains("Persistent shareable link") + dd a');
		    if (!$plink->count()) {
		        throw new \LogicException("Missing Persistent shareable link for $name");
		    }
		    $url = $plink->attr('href');

		    if (!preg_match('|https://museumdata.uk/museums/(q\d+)/|i', $url, $matches)) {
		        throw new \LogicException("Missing q code in persistent link: $url");
		    }
		    $code = strtolower($matches[1]);
            $grp = $this->createGrp($code, $name);
		    $grp->persistentLink = $url;

		    // Wikidata ID
		    $wikidata = $museumNode->filter('dt:contains("Wikidata identifier:") + dd a');
		    $grp->wikidataId = $wikidata->count() ? basename($wikidata->attr('href')) : null;

		    // Status
		    $statusNode = $museumNode->filter('dt:contains("Museum/collection status:") + dd');
		    $grp->status = $statusNode->count() ? trim($statusNode->text()) : null;

		    // Aliases
		    $aliasesNode = $museumNode->filter('dt:contains("Also known as:") + dd');
		    $grp->aliases = $aliasesNode->count() ? trim($aliasesNode->text()) : null;

		    // Object Records
		    $objectRecordsNode = $museumNode->filter('dt:contains("Object records:") + dd');
		    $objectRecords = $objectRecordsNode->count() ? $objectRecordsNode->text() : '';
		    $grp->hasObjectRecords = str_contains(strtolower($objectRecords), 'yes');

		    // Collection Overview (text inside the first .collection-record__content)
		    $overviewNode = $museumNode->filter('.collection-record__content');
		    $grp->description = $overviewNode->count() ? trim($overviewNode->text()) : null;

		    // Licence (from the collection-record__content paragraphs)
		    $licenceNode = $museumNode->filter('.collection-record__content p:contains("Licence:")');
		    $grp->licence = $licenceNode->count()
		        ? trim(str_replace('Licence:', '', $licenceNode->text()))
		        : null;

		    $groups[] = $grp;
		});


        return $groups;
	}


	private function createGrp(string $id, string $name): Grp
	{
		if (!$grp = $this->grpRepository->find($id)) {
		    $grp = new Grp($id, $name);
		    $this->entityManager->persist($grp);
		}
        return $grp;
	}


}
