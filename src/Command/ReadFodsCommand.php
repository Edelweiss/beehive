<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DateTime;
use DOMDocument;
use DOMXPath;
use App\Entity\Hgv;
use App\Entity\PictureLink;
use App\Service\Fods;

ini_set('memory_limit', -1);

class ReadFodsCommand extends Command
{
    const NAMESPACE_FILEMAKER = 'http://www.filemaker.com/fmpxmlresult';
    const NAMESPACE_TEI       = 'http://www.tei-c.org/ns/1.0';
    const NAMESPACE_TABLE     = 'urn:oasis:names:tc:opendocument:xmlns:table:1.0';
    const IMPORT_DIR          = __DIR__ . '/../../data/';

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:read-fods';

    private $fods;


    function __construct(Fods $fods){
	    $this->fods = $fods;
        parent::__construct();
    }

    protected function configure(): void
    {
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        echo '1234 import dir: ' . self::IMPORT_DIR . "\n";
	$importFile = '/var/www/beehive/data/import_bl_cd_ostraca.fods';
	$tableName = 'Ostraca';
	$headerLine = 1;
	$headerKey = '';
	$fieldNames = ['compilation_id', 'edition_id', 'register_id', 'position', 'compilationPage', 'description'];

	$this->fods->getArray($importFile, $tableName, $headerLine, $headerKey, $fieldNames);

	return Command::SUCCESS;
        // return Command::FAILURE;
        // return Command::INVALID;
    }


}
