<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Register;
use App\Entity\Volume;
use SimpleXMLElement;

class LinkRegisterToVolumeCommand extends Command
{
    protected static $defaultName = 'app:link-register-to-volume';
    private EntityManagerInterface $entityManager;
    private OutputInterface $output;
    private bool $dryRun = false;
    private array $editions = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setDescription('Links register entries to volume entries and creates missing volumes');
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Run without persisting changes to the database'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->dryRun = $input->getOption('dry-run');

        $output->writeln('');
        $output->writeln('<info>========================================</info>');
        $output->writeln('<info>Link Register to Volume Command</info>');
        $output->writeln($this->dryRun ? '<comment>[DRY RUN MODE]</comment>' : '<info>[PRODUCTION MODE]</info>');
        $output->writeln('<info>========================================</info>');
        $output->writeln('');

        try {
            // Step 1: Load edition mapping from RDF
            $this->log('Step 1: Loading edition data from RDF...');
            $this->loadEditionMapping();
            $this->log("  ✓ Loaded " . count($this->editions) . " editions");
            $output->writeln('');

            // Step 2: Link existing volumes
            $this->log('Step 2: Linking register entries with existing volumes...');
            $linkedCount = $this->linkExistingVolumes();
            $this->log("  ✓ Linked $linkedCount register entries");
            $output->writeln('');

            // Step 3: Find unlinked entries
            $this->log('Step 3: Finding unlinked register entries...');
            $unlinked = $this->findUnlinkedVolumes();
            $this->log("  ✓ Found " . count($unlinked) . " unlinked volume identifiers");
            $output->writeln('');

            if (empty($unlinked)) {
                $this->log('<info>✓ All register entries are now linked!</info>');
                return Command::SUCCESS;
            }

            // Step 4: Create missing volumes
            $this->log('Step 4: Creating missing volume entries...');
            $this->createMissingVolumes($unlinked);
            $output->writeln('');

            $this->log('<info>========================================</info>');
            if ($this->dryRun) {
                $this->log('<comment>DRY RUN: No changes were persisted to the database</comment>');
            } else {
                $this->log('<info>✓ Command completed successfully!</info>');
            }
            $this->log('<info>========================================</info>');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            if ($input->getOption('verbose')) {
                $output->writeln($e->getTraceAsString());
            }
            return Command::FAILURE;
        }
    }

    private function loadEditionMapping(): void
    {
        $rdfPath = dirname(__DIR__, 2) . '/idp.data/RDF/collection.rdf';

        if (!file_exists($rdfPath)) {
            throw new \Exception("RDF file not found at: $rdfPath");
        }

        $xml = simplexml_load_file($rdfPath);
        if ($xml === false) {
            throw new \Exception("Failed to parse RDF file");
        }

        foreach ($xml->xpath('//rdf:Description') as $description) {
            $about = (string)$description->attributes('rdf', true)->about;
            $citation = $description->xpath('dcterms:bibliographicCitation');

            if (!empty($citation)) {
                $citationText = (string)$citation[0];
                preg_match('#/editions/([^/]+)$#', $about, $matches);
                if (!empty($matches[1])) {
                    $this->editions[$matches[1]] = $citationText;
                }
            }
        }
    }

    private function linkExistingVolumes(): int
    {
        $conn = $this->entityManager->getConnection();
        $linkedCount = 0;

        // First UPDATE: Link via ddb
        $sql1 = "UPDATE register r JOIN volume v ON SUBSTRING_INDEX(r.ddb, ';', 2) = v.hybrid 
                 SET r.volume_id = v.id 
                 WHERE r.volume_id IS NULL AND r.ddb IS NOT NULL";
        $linkedCount += $conn->executeStatement($sql1);

        // Second UPDATE: Link via dclp
        $sql2 = "UPDATE register r JOIN volume v ON SUBSTRING_INDEX(r.dclp, ';', 2) = v.hybrid 
                 SET r.volume_id = v.id 
                 WHERE r.volume_id IS NULL AND r.ddb IS NULL AND r.dclp IS NOT NULL";
        $linkedCount += $conn->executeStatement($sql2);

        if (!$this->dryRun) {
            $this->entityManager->flush();
        }

        return $linkedCount;
    }

    private function findUnlinkedVolumes(): array
    {
        $conn = $this->entityManager->getConnection();

        $sql = "SELECT DISTINCT SUBSTRING_INDEX(IF(ddb IS NOT NULL, ddb, dclp), ';', 2) as hybrid 
                FROM register 
                WHERE volume_id IS NULL 
                AND (ddb IS NOT NULL OR dclp IS NOT NULL)
                ORDER BY hybrid";

        $result = $conn->fetchAllAssociative($sql);

        return array_column($result, 'hybrid');
    }

    private function createMissingVolumes(array $unlinked): void
    {
        $existingVolumes = $this->getExistingVolumes();
        $newVolumes = [];
        $skippedCount = 0;

        foreach ($unlinked as $hybrid) {
            if (isset($existingVolumes[$hybrid])) {
                $this->log("  ✓ $hybrid already exists, skipping");
                continue;
            }

            [$editionKey, $number] = $this->parseHybrid($hybrid);

            if (!isset($this->editions[$editionKey])) {
                $this->log("  ⚠ <comment>Edition not found in RDF: $editionKey (from $hybrid)</comment>");
                $skippedCount++;
                continue;
            }

            $title = $this->editions[$editionKey];
            if (!empty($number)) {
                $title .= ' ' . $number;
            }

            $sortNumber = $this->calculateSortNumber($hybrid, $editionKey, $number, $existingVolumes);

            $volume = [
                'hybrid' => $hybrid,
                'title' => $title,
                'sort' => $sortNumber,
            ];

            $newVolumes[] = $volume;

            // Log with preceding/following records
            $preceding = $this->getPrecedingVolume($hybrid, $existingVolumes, $sortNumber);
            $following = $this->getFollowingVolume($hybrid, $existingVolumes, $sortNumber);

            $log = "  ✓ <fg=green>NEW:</> $hybrid -> '$title' (sort: $sortNumber)";
            if ($preceding) {
                $log .= "\n      preceding: {$preceding['hybrid']} (sort: {$preceding['sort']})";
            }
            if ($following) {
                $log .= "\n      following: {$following['hybrid']} (sort: {$following['sort']})";
            }

            $this->log($log);

            if (!$this->dryRun) {
                $this->persistVolume($volume);
            }
        }

        $this->log('');
        if ($skippedCount > 0) {
            $this->log("  <comment>Skipped: $skippedCount entries with missing edition data</comment>");
        }

        if ($this->dryRun && !empty($newVolumes)) {
            $this->log("  <comment>DRY RUN: Would create " . count($newVolumes) . " new volumes</comment>");
        } elseif (!$this->dryRun && !empty($newVolumes)) {
            $this->log("  <info>✓ Created " . count($newVolumes) . " new volumes</info>");
        }
    }

    private function parseHybrid(string $hybrid): array
    {
        $parts = explode(';', $hybrid, 2);
        return [
            $parts[0],
            $parts[1] ?? ''
        ];
    }

    private function getExistingVolumes(): array
    {
        $conn = $this->entityManager->getConnection();
        $sql = "SELECT id, hybrid, sort FROM volume ORDER BY sort ASC";
        $result = $conn->fetchAllAssociative($sql);

        $volumes = [];
        foreach ($result as $row) {
            $volumes[$row['hybrid']] = [
                'id' => $row['id'],
                'sort' => $row['sort'],
                'hybrid' => $row['hybrid'],
            ];
        }

        return $volumes;
    }

    private function calculateSortNumber(
        string $hybrid,
        string $editionKey,
        string $number,
        array $existingVolumes
    ): int {
        $precedingSort = null;

        foreach ($existingVolumes as $existing) {
            if ($this->isSameEdition($existing['hybrid'], $editionKey)) {
                $existingNumber = $this->extractNumber($existing['hybrid']);

                if ($existingNumber !== null && $number !== null) {
                    if ($existingNumber < (int)$number) {
                        if ($precedingSort === null || $existing['sort'] > $precedingSort) {
                            $precedingSort = $existing['sort'];
                        }
                    }
                }
            }
        }

        if ($precedingSort !== null) {
            return $precedingSort + 1;
        }

        $maxSort = 0;
        foreach ($existingVolumes as $existing) {
            if ($existing['sort'] > $maxSort) {
                $maxSort = $existing['sort'];
            }
        }

        return $maxSort + 1;
    }

    private function isSameEdition(string $hybrid, string $editionKey): bool
    {
        return strpos($hybrid, $editionKey . ';') === 0;
    }

    private function extractNumber(string $hybrid): ?int
    {
        $parts = explode(';', $hybrid, 2);
        return isset($parts[1]) && is_numeric($parts[1]) ? (int)$parts[1] : null;
    }

    private function getPrecedingVolume(string $hybrid, array $volumes, int $sortNumber): ?array
    {
        $best = null;

        foreach ($volumes as $vol) {
            if ($vol['sort'] < $sortNumber) {
                if ($best === null || $vol['sort'] > $best['sort']) {
                    $best = $vol;
                }
            }
        }

        return $best;
    }

    private function getFollowingVolume(string $hybrid, array $volumes, int $sortNumber): ?array
    {
        $best = null;

        foreach ($volumes as $vol) {
            if ($vol['sort'] >= $sortNumber) {
                if ($best === null || $vol['sort'] < $best['sort']) {
                    $best = $vol;
                }
            }
        }

        return $best === null || $best['sort'] === $sortNumber ? null : $best;
    }

    private function persistVolume(array $volumeData): void
    {
        $conn = $this->entityManager->getConnection();
        $sql = "INSERT INTO volume (hybrid, title, sort) VALUES (:hybrid, :title, :sort)";

        $conn->executeStatement($sql, [
            'hybrid' => $volumeData['hybrid'],
            'title' => $volumeData['title'],
            'sort' => $volumeData['sort'],
        ]);
    }

    private function log(string $message): void
    {
        $this->output->writeln($message);
    }
}
