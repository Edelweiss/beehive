<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use DateTime;
use DOMDocument;
use DOMXPath;
use App\Service\Fods;

ini_set('memory_limit', -1);

class ReadFodsCommand extends Command
{
    const NAMESPACE_FILEMAKER = 'http://www.filemaker.com/fmpxmlresult';
    const NAMESPACE_TEI       = 'http://www.tei-c.org/ns/1.0';
    const NAMESPACE_TABLE     = 'urn:oasis:names:tc:opendocument:xmlns:table:1.0';
    const IMPORT_DIR          = __DIR__ . '/../../data/';
    const IMPORT_FILE         = 'import.fods';
    const TABLE_NAME          = 'beehive';
    const HEADER_LINE         = 1;


    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:read-fods';

    private $fods;
    protected $file = null;
    protected $fieldNames = ['compilation_id', 'compilation_page', 'register_id', 'edition_id', 'text', 'position', 'description', 'source', 'creator', 'task_ddb'];
    protected $dataTable = [];

    function __construct(Fods $fods){
	    $this->fods = $fods;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'name of the file to be imported; provide just a name if it resides in the data directory or an absolute  path if it`s to be found somewhere else', self::IMPORT_DIR . '/' . self::IMPORT_FILE);
        $this->addOption('table-name', null, InputOption::VALUE_REQUIRED, 'name of the tab or sheet within the FODS file', self::TABLE_NAME);
        $this->addOption('header-line', null, InputOption::VALUE_REQUIRED, 'line in which the column titles are', self::HEADER_LINE);
        $this->addOption('header-key', null, InputOption::VALUE_REQUIRED, 'string by which to identify the line in which the column titles are (alternative to specifying the header line)', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
	    if(strpos($input->getOption('file'), '/') === 0){
            $this->file = $input->getOption('file');
            
        } else {
            $this->file = self::IMPORT_DIR . '/' . $input->getOption('file');
        }
        $this->dataTable = $this->fods->getArray($this->file, $input->getOption('table-name'), $input->getOption('header-line'), $input->getOption('header-key'), $this->fieldNames);
        

	    return Command::SUCCESS;
        // return Command::FAILURE;
        // return Command::INVALID;
    }


}
