<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use DateTime;
use App\Entity\Register;
use Doctrine\ORM\EntityManagerInterface;

ini_set('memory_limit', -1);

class UpdateRegisterCommand extends Command
{
    const IMPORT_DIR = __DIR__ . '/../../data/';

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:update-register';
    protected $entityManager;
    protected $dryRun = false;

    function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
        echo ' import dir: ' . self::IMPORT_DIR . "\n";
        echo " = = = = = = = = = = = = \n";
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('dry_run', InputArgument::OPTIONAL, 'Just simulate.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dryRun = preg_match('#^dry[-_]?[rR]un$#', $input->getArgument('dry_run')) ? true : false;
        $idnos = fopen(self::IMPORT_DIR . 'idno.csv', 'r');
        if($idnos === FALSE) {
            return Command::INVALID;
        }

        $row = $update = $new = 1;
        while (($data = fgetcsv($idnos, 1024, '|')) !== FALSE) {
            if(count($data) < 3){
                echo str_pad($row, 6, ' ', STR_PAD_LEFT) . ': FEHLER, ungültige Zeile (' . implode('|', $data) . ')' . "\n";
                continue;
            }

            $hgv = trim($data[0]);
            $tm = trim($data[1]);
            $ddb = trim($data[2]);
            $dclp = isset($data[3]) ? trim($data[3]) : null;
            $idnoInfo = str_pad($row, 6, ' ', STR_PAD_LEFT) . ': ' . ($hgv ? $hgv : $tm) . ($ddb || $dclp ? '/' . $ddb . ($ddb && $dclp ? '/' : '') . $dclp : '');

            if($hgv && (intval($hgv) >= 500000) && (intval($hgv) <= 501000)){
                echo $idnoInfo . ' WARNUNG, HGV-Nummer wird ignoriert' . "\n";
                continue;
            }

            if($hgv && !preg_match('/^\d+[a-z]*$/', $hgv)){
                echo $idnoInfo . ' FEHLER, ungültige HGV-Nummer' . "\n";
                continue;
            }

            if($tm && !preg_match('/^\d+$/', $tm)){
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

            //$repository = $this->entityManager->getRepository(Register::class);
            $findMatchingRegisterEntry = $this->entityManager->createQuery('SELECT r.id, r.ddb, r.dclp FROM App\Entity\Register r ' . ' WHERE r.hgv = ' . "'" . $hgv . "'" . ' OR (r.hgv IS NULL AND r.tm = ' . "'" . $tm . "'" . ')');
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
                echo $idnoInfo . ' FEHLER, TM/HGV-Nummer in Datenbank nicht unique' . "\n";
            }
            $row++;
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