<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use DateTime;
use App\Entity\Register;
use Doctrine\ORM\EntityManagerInterface;

ini_set('memory_limit', -1);

class UpdateRegisterCommand extends Command
{
    const IMPORT_DIR = __DIR__ . '/../../data';
    const SEPARATOR = ',';
    const HEADER = '1';

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:update-register';
    protected $entityManager;
    protected $dryRun = false;

    static function fallback($value, $fallback){
      if(!isset($value) || $value === null || $value === '')
      {
        return $fallback;
      }
      return $value;
    }

    function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
        echo ' import dir: ' . self::IMPORT_DIR . "\n";
        echo " = = = = = = = = = = = = \n";
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('dry_run', InputArgument::OPTIONAL, 'Just simulate.');
        $this->addOption('separator', 's', InputOption::VALUE_REQUIRED, 'separator by which the individual fields in the csv file are separated', self::SEPARATOR);
        $this->addOption('header', null, InputOption::VALUE_REQUIRED, 'amount of header lines which need to be skipped to access the data', self::HEADER);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dryRun = preg_match('#^dry[-_]?[rR]un$#', $input->getArgument('dry_run')) ? true : false;
        $idnos = fopen(self::IMPORT_DIR . '/' . 'idnos.csv', 'r');
        if($idnos === FALSE) {
            return Command::INVALID;
        }

        for($header = 0; $header < $input->getOption('header'); $header++){
            fgetcsv($idnos, 1024, $input->getOption('separator'));
        }

        $row = 0;
        $update = $new = 0;
        while (($data = fgetcsv($idnos, 1024, $input->getOption('separator'))) !== FALSE) {
            $row++;
            if(count($data) < 3){
                echo str_pad($row, 6, ' ', STR_PAD_LEFT) . ': FEHLER, ungültige Zeile (' . implode('|', $data) . ')' . "\n";
                continue;
            }

            $hgv = self::fallback(trim($data[0]), null);
            $tm = self::fallback(trim($data[1]), null);
            $ddb = self::fallback(trim($data[2]), null);
            $dclp = self::fallback(trim($data[3]), null);
            $idnoInfo = str_pad($row, 6, ' ', STR_PAD_LEFT) . ': ' . ($hgv ? $hgv : $tm) . ($ddb || $dclp ? '/' . $ddb . ($ddb && $dclp ? '/' : '') . $dclp : '');

            if($hgv && (intval($hgv) >= 500000) && (intval($hgv) <= 500100)){
                echo $idnoInfo . ' WARNUNG, HGV-Nummer wird ignoriert' . "\n";
                continue;
            }

            if($hgv && !preg_match('/^\d+[a-z]*$/', $hgv)){
                echo $idnoInfo . ' FEHLER, ungültige HGV-Nummer' . "\n";
                continue;
            }

            if(!preg_match('/^\d+$/', $tm)){
                echo $idnoInfo . ' FEHLER, ungültige TM-Nummer' . "\n";
                continue;
            }

            if($ddb && preg_match('/^sosol;/', $ddb)){
                echo $idnoInfo . ' FEHLER, SoSOL-DDB-hybrid' . "\n";
                continue;
            }

            if($dclp && preg_match('/^sosol;/', $dclp)){
                echo $idnoInfo . ' FEHLER, SoSOL-DCLP-hybrid' . "\n";
                continue;
            }

            if($hgv && $tm && (preg_replace('/[a-z]+/', '', $hgv) + 0 != $tm)){
                echo $idnoInfo . ' FEHLER, TM-Nummer weicht von HGV-Nummer ab' . "\n";
                continue;
            }

# Neueste noch nicht verknüpfte Einträge löschen
# DELETE FROM register WHERE id > 76935

# Crap finden
# SELECT * FROM register WHERE tm IS NULL OR tm = '' OR tm = 0 OR hgv = '' OR hgv = '0'

            // SELECT * FROM `register` r WHERE r.hgv = '100111' OR (r.hgv IS NULL AND r.tm = '100111')
            $findMatchingRegisterEntry = $this->entityManager->createQuery('SELECT r.id, r.hgv, r.tm, r.ddb, r.dclp FROM App\Entity\Register r ' . ' WHERE r.hgv = ' . "'" . $hgv . "'" . ' OR (r.hgv IS NULL AND r.tm = ' . "'" . $tm . "'" . ')');
            $matchingRegisterEntry = $findMatchingRegisterEntry->getResult();

            if(count($matchingRegisterEntry) === 1){ // UPDATE
                if(($matchingRegisterEntry[0]['ddb'] != $ddb) OR ($matchingRegisterEntry[0]['dclp'] != $dclp)){
                    $setDdb  = $ddb  ? 'r.ddb = ' . "'" . $ddb . "'"   : 'r.ddb = NULL';
                    $setDclp = $dclp ? 'r.dclp = ' . "'" . $dclp . "'" : 'r.dclp = NULL';

                    $idnoInfo .= ' (ALT: ' . $matchingRegisterEntry[0]['ddb'] . '/' . $matchingRegisterEntry[0]['dclp'] . ')' . ' ……… aktualisiert';

                    $updateQuery = $this->entityManager->createQuery('UPDATE App\Entity\Register r SET ' . $setDdb . ', '  . $setDclp . ' WHERE r.hgv = ' . "'" . $hgv . "'" . ' OR (r.hgv IS NULL AND r.tm = ' . "'" . $tm . "'" . ')');
                    if(!$this->dryRun){
                       $updated = $updateQuery->getResult();
                       if($updated){
                           echo $idnoInfo . "\n";
                       }
                    } else {
                        echo $idnoInfo . "\n";
                    }
                    $update++;
                }
            } elseif(count($matchingRegisterEntry) === 0){ // NEW
                $register = new Register();
                $register->setHgv($hgv);
                $register->setTm($tm);
                $register->setDdb($ddb);
                $register->setDclp($dclp);
                if(!$this->dryRun){
                    $this->entityManager->persist($register);
                    $this->entityManager->flush();
                }
                echo $idnoInfo . ' ……… neuer Registereintrag angelegt' . "\n";
                $new++;
            } else {
            var_dump($matchingRegisterEntry);
                echo $idnoInfo . ' FEHLER, TM/HGV-Nummer in Datenbank nicht unique' . "\n";
            }
        }
        fclose($idnos);

        echo ' = = = = = = = = = = = = = = = = '  . "\n";
        echo str_pad($row, 6, ' ', STR_PAD_LEFT) . ' Zeilen bearbeitet'  . "\n";
        echo str_pad($update, 6, ' ', STR_PAD_LEFT) . ' Daten aktualisiert'  . "\n";
        echo str_pad($new, 6, ' ', STR_PAD_LEFT) . ' Datensätze angelegt'  . "\n";

	    return Command::SUCCESS;
        // return Command::FAILURE;
        // return Command::INVALID;
    }

}