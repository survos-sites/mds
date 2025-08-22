<?php
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\Option;
use Phpoaipmh\Endpoint;

#[AsCommand('oai:harvest', 'Harvest an OAI-PMH endpoint to NDJSON (records or identifiers)')]
final class OaiHarvestCommand
{
    public function __construct(
    )
    {

    }
    public function __invoke(
        SymfonyStyle $io,

        #[Argument('Output file path (or "-" for stdout)')]
        string $out = '-',

        #[Option('OAI-PMH base URL (e.g. https://data.rijksmuseum.nl/oai)', 'b')]
        string $base = 'https://data.rijksmuseum.nl/oai',

        #[Option('metadataPrefix (e.g. oai_dc, edm, marc21)', 'p')]
        string $prefix = 'oai_dc',

        #[Option('setSpec to filter (optional)', 's')]
        ?string $set = null,

        #[Option('From date YYYY-MM-DD (optional)', 'f')]
        ?string $from = null,

        #[Option('Until date YYYY-MM-DD (optional)', 'u')]
        ?string $until = null,

        #[Option('Identifiers only (ListIdentifiers)', 'i')]
        bool $identifiersOnly = false,

        #[Option('Max records to emit (0 = all)', 'm')]
        int $max = 0,
    ): int {
        $writer = $out === '-' ? null : new \SplFileObject($out, 'w');

        // Build client
        $endpoint = Endpoint::build($base);
        $io->title($base);

        // Resolve optional dates
        $fromDt  = $from  ? new \DateTimeImmutable($from)  : null;
        $untilDt = $until ? new \DateTimeImmutable($until) : null;

        $count = 0;

        if ($identifiersOnly) {
            $iter = $endpoint->listIdentifiers($prefix, $fromDt, $untilDt, $set);
            foreach ($iter as $header) {
                $row = [
                    'verb'      => 'ListIdentifiers',
                    'identifier'=> (string)($header->identifier ?? ''),
                    'datestamp' => (string)($header->datestamp ?? ''),
                    'setSpec'   => array_map('strval', iterator_to_array($header->setSpec ?? [])),
                ];
                $this->emit($row, $writer, $io);
                if ($max > 0 && ++$count >= $max) break;
            }
        } else {
            $iter = $endpoint->listRecords($prefix, $fromDt, $untilDt, $set);
            foreach ($iter as $rec) {
                // Header
                $header = $rec->header ?? null;
                $identifier = $header ? (string)$header->identifier : '';
                $datestamp  = $header ? (string)$header->datestamp  : '';
                $sets = $header ? array_map('strval', iterator_to_array($header->setSpec ?? [])) : [];

                // Try to parse common OAI DC fields if present; keep raw metadata XML too
                [$dc, $rawXml] = $this->parseOaiDc($rec);

                $row = [
                    'verb'       => 'ListRecords',
                    'identifier' => $identifier,
                    'datestamp'  => $datestamp,
                    'setSpec'    => $sets,
                    'metadata'   => $dc ?: null,     // normalized DC if available
                    '_raw'       => $rawXml,         // raw XML string (namespaced)
                ];

                $this->emit($row, $writer, $io);
                if ($max > 0 && ++$count >= $max) break;
            }
        }

        $io->success(sprintf('Emitted %d line(s) → %s', $count, $out));
        return 0;
    }

    private function emit(array $row, ?\SplFileObject $file, SymfonyStyle $io): void
    {
        $line = json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        if ($file) { $file->fwrite($line); } else { $io->write($line); }
    }

    /**
     * Attempt to extract Dublin Core from <metadata><oai_dc:dc>…</oai_dc:dc>
     * Returns [array|null, string rawXml]
     */
    private function parseOaiDc(\SimpleXMLElement $rec): array
    {
        $rawXml = isset($rec->metadata) ? $rec->metadata->asXML() : null;

        if (!isset($rec->metadata)) {
            return [null, $rawXml];
        }

        $ns = $rec->metadata->getNamespaces(true);
        $oaiDcNs = $ns['oai_dc'] ?? null;
        $dcNs    = $ns['dc']     ?? null;

        if (!$oaiDcNs || !$dcNs) {
            // Not oai_dc (could be edm, marc21, etc.)
            return [null, $rawXml];
        }

        $dcEl = $rec->metadata->children($oaiDcNs)->dc ?? null;
        if (!$dcEl) {
            return [null, $rawXml];
        }

        $dc = [];
        $children = $dcEl->children($dcNs);
        foreach ($children as $name => $value) {
            $dc[(string)$name][] = trim((string)$value);
        }
        // Collapse singletons
        foreach ($dc as $k => $vals) {
            if (is_array($vals) && count($vals) === 1) {
                $dc[$k] = $vals[0];
            }
        }
        return [$dc, $rawXml];
    }
}
