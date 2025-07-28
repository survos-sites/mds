<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class MuseumExtractor
{
    public function extract(string $html): array
    {
        $museums = [];
        $crawler = new Crawler($html);

        $crawler->filter('details.museum-overview')->each(function (Crawler $node) use (&$museums) {
            $museum = [];

            $museum['name'] = $node->filter('summary h2')->count()
                ? trim($node->filter('summary h2')->text())
                : null;

            $museum['wikidata_id'] = $node->filter('a[href*="wikidata.org/wiki"]')->count()
                ? basename($node->filter('a[href*="wikidata.org/wiki"]')->attr('href'))
                : null;

            $instanceOf = $node->filter('dt:contains("Instance of:") + dd')->count()
                ? $node->filter('dt:contains("Instance of:") + dd')->text()
                : '';
            $museum['instanceOf'] = $instanceOf
                ? array_map('trim', preg_split('/[;,]/', $instanceOf))
                : [];

            $museum['status'] = $node->filter('dt:contains("Museum/collection status:") + dd')->count()
                ? trim($node->filter('dt:contains("Museum/collection status:") + dd')->text())
                : null;

            $museum['persistent_link'] = $node->filter('dt:contains("Persistent shareable link for this record:") + dd a')->count()
                ? $node->filter('dt:contains("Persistent shareable link for this record:") + dd a')->attr('href')
                : null;

            $museum['description'] = $node->filter('li.collection-record div.collection-record__content')->count()
                ? trim($node->filter('li.collection-record div.collection-record__content')->text())
                : null;

            $museums[] = $museum;
            dd($museum);
        });

        return $museums;
    }
}