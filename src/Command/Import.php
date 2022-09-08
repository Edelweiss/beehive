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

ini_set('memory_limit', -1);

class ImportCommand extends Command
{
    const NAMESPACE_FILEMAKER = 'http://www.filemaker.com/fmpxmlresult';
    const NAMESPACE_TEI       = 'http://www.tei-c.org/ns/1.0';
    const NAMESPACE_TABLE     = 'urn:oasis:names:tc:opendocument:xmlns:table:1.0';
    const IMPORT_DIR          = __DIR__ . '/../../data/';

    protected $importFile = 'hgv.fods';
    protected $tableName = 'hgv';
    protected $headerKey = 'tm_id';
    protected $headerLine = 1;
    protected $flushCounter = 0;
    protected $xpath = null;

    protected $positions = array();

    protected $fields = array(
      'tmNr'            => 'tm_id',
      'texLett'         => 'hgv_letter',
      'mehrfachKennung' => 'multiple_letter',
      'texIdLang'       => 'hgv_id_long', // TmNr + texLett + mehrfachKennung
      'publikation'     => 'publication',
      'band'            => 'volume',
      'zusBand'         => 'volume_extra',
      'nummer'          => 'number',
      'seite'           => 'side',
      'zusaetzlich'     => 'number_extra',
      'publikationLang' => 'publication_long',

      'invNr'           => 'inventory_number',
      'place'           => 'place',
      'collection'      => 'collection',
      //place
      //collection

      'jahr'            => 'year_1',
      'monat'           => 'month_1',
      'tag'             => 'day_1',
      'jh'              => 'century_1',
      'erg'             => 'extra_1',
      'jahrIi'          => 'year_2',
      'monatIi'         => 'month_2',
      'tagIi'           => 'day_2',
      'jhIi'            => 'century_2',
      'ergIi'           => 'extra_2',
      'unsicher'        => 'uncertain',

      'datierung'       => 'date_1',
      'datierungIi'     => 'date_2',

      'chronMinimum'    => 'chron_minimum',
      'chronMaximum'    => 'chron_maximum',
      'chronGlobal'     => 'chron_global',

      'erwaehnteDaten'  => 'mentioned_dates',

      'ort'             => 'provenance',
      //provenance_type
      //place_name
      //nome
      //province
      //region

      'material'        => 'material',

      'originaltitel'   => 'original_title',
      'inhalt'          => 'content',
      'anderePublikation' => 'other_publication',
      'uebersetzungen'  => 'translations',
      'abbildung'       => 'published_photo',
      'linkFm'          => 'image_link',

      'bemerkungen'     => 'notes',
      'ddbSerIdp'       => 'ddb_collection_hybrid',
      'ddbVol'          => 'ddb_volume_number',
      'ddbDoc'          => 'ddb_document_number',

      'eingegebenAm'    => 'created'
    );

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:import';

    function __construct(){
        parent::__construct();
    }

    protected function configure(): void
    {
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        echo 'import dir: ' . self::IMPORT_DIR . "\n";
        return Command::SUCCESS;
        // return Command::FAILURE;
        // return Command::INVALID;
    }

    protected function loadFodsData($fodsFile)
    {
      // xpath
      $doc = new DOMDocument();
      $doc->load($fodsFile);
      $xpath = new DOMXPath($doc);
      $xpath->registerNamespace('table', self::NAMESPACE_TABLE);
      $this->xpath = $xpath;

      // column positions
      $this->headerLine = count($this->xpath->evaluate("//table:table-cell[normalize-space(.) = '" . $this->headerKey . "']/ancestor::table:table-row/preceding-sibling::table:table-row")) + 1;
      foreach($this->xpath->evaluate("//table:table[@table:name='" . $this->tableName . "']//table:table-row[" . $this->headerLine . "]//table:table-cell") as $position => $index){
        $name = trim($index->nodeValue);
        $column =  intval($position + 1 + $this->xpath->evaluate('sum(./preceding-sibling::table:table-cell/@table:number-columns-repeated) - count(preceding-sibling::table:table-cell[number(@table:number-columns-repeated) > 1])', $index));
        if(in_array($name, $this->fields)){
          $this->positions[$name] = $column;
        }
      }
      foreach($this->positions as $key => $position){
        echo $key . ' > ' . $position . "\n";
      }
    }
/*
    protected static function makeInteger($fmInt){
      return str_replace(' ', '', $fmInt);
    }
*/
    protected static function makeDate($germanDate){
      if(preg_match('/\d\d.\d\d.\d\d\d\d/', $germanDate)){
        return new DateTime(substr($germanDate, 6, 4) . '-' . substr($germanDate, 3, 2) . '-' . substr($germanDate, 0, 2));
      }
      return new DateTime();
    }

    protected function getValue($row, $fieldName){
      $fieldIndex = $this->positions[$fieldName];
      //$nodeList = $this->xpath->evaluate('table:table-cell[(count(preceding-sibling::table:table-cell[not(@table:number-columns-repeated)]) + sum(preceding-sibling::table:table-cell/@table:number-columns-repeated) + (1)) >= ' . $fieldIndex . ']', $row);
      $nodeList = $this->xpath->evaluate('table:table-cell[(count(preceding-sibling::table:table-cell[not(@table:number-columns-repeated)]) + sum(preceding-sibling::table:table-cell/@table:number-columns-repeated) + (1 - count(@table:number-columns-repeated) + sum(@table:number-columns-repeated))) >= ' . $fieldIndex . ']', $row);
      if(!$nodeList->item(0)){
        echo '----------------------------------------------- ' . $fieldName . '/' . $fieldIndex . "\n";
        foreach($this->xpath->evaluate('table:table-cell', $row) as $node){
          echo trim($node->nodeValue) . " >> ";
          foreach($node->attributes as $att){
            if($att->name == 'number-columns-repeated'){
              echo '[@' . $att->name . '=' . $att->value . ']';
            }
          }
          echo "\n";
        }
        dd($row);
      }
      return trim($nodeList->item(0)->nodeValue);
    }

    protected function generateObjectFromXml($row, $hgv = null){
      if($hgv === null){
        $hgv = new Hgv($this->getValue($row, 'hgv_id_long'));
        $hgv->setCreatedAt(new DateTime());
      }
      $hgv->setModifiedAt(new DateTime());
      $hgv->settmNr($this->getValue($row, 'tm_id'));
      $hgv->settexLett($this->getValue($row, 'hgv_letter'));
      $hgv->setmehrfachKennung($this->getValue($row, 'multiple_letter'));
      $hgv->setpublikation($this->getValue($row, 'publication'));
      $hgv->setband($this->getValue($row, 'volume'));
      $hgv->setzusBand($this->getValue($row, 'volume_extra'));
      $hgv->setnummer($this->getValue($row, 'number'));
      $hgv->setzusaetzlich($this->getValue($row, 'number_extra'));
      $hgv->setseite($this->getValue($row, 'side'));
      $hgv->setpublikationLang($this->getValue($row, 'publication_long'));
      $hgv->setmaterial($this->getValue($row, 'material'));
      #$hgv->setzusaetzlichSort($this->getValue($row, 'xxx'));
      $hgv->setinvNr($this->getValue($row, 'place') . ', ' . $this->getValue($row, 'collection') . ', ' . $this->getValue($row, 'inventory_number'));
      $hgv->setjahr($this->getValue($row, 'year_1'));
      $hgv->setmonat($this->getValue($row, 'month_1'));
      $hgv->settag($this->getValue($row, 'day_1'));
      $hgv->setjh($this->getValue($row, 'century_1'));
      $hgv->seterg($this->getValue($row, 'extra_1'));
      $hgv->setjahrIi($this->getValue($row, 'year_2'));
      $hgv->setmonatIi($this->getValue($row, 'month_2'));
      $hgv->settagIi($this->getValue($row, 'day_2'));
      $hgv->setjhIi($this->getValue($row, 'century_2'));
      $hgv->setergIi($this->getValue($row, 'extra_2'));
      $hgv->setunsicher($this->getValue($row, 'uncertain'));
      $hgv->setchronMinimum($this->getValue($row, 'chron_minimum'));
      $hgv->setchronMaximum($this->getValue($row, 'chron_maximum'));
      $hgv->setchronGlobal($this->getValue($row, 'chron_global'));
      $hgv->seterwaehnteDaten($this->getValue($row, 'mentioned_dates'));
      $hgv->setdatierung($this->getValue($row, 'date_1'));
      $hgv->setdatierungIi($this->getValue($row, 'date_2'));
      $hgv->setanderePublikation($this->getValue($row, 'other_publication'));
      #$hgv->setbl($this->getValue($row, 'xxx'));
      #$hgv->setblOnline($this->getValue($row, 'xxx'));
      $hgv->setuebersetzungen($this->getValue($row, 'translations'));
      $hgv->setabbildung($this->getValue($row, 'published_photo'));
      $hgv->setort($this->getValue($row, 'provenance'));
      $hgv->setoriginaltitel($this->getValue($row, 'original_title'));
      $hgv->setoriginaltitelHtml($this->getValue($row, 'original_title'));
      $hgv->setinhalt($this->getValue($row, 'content'));
      $hgv->setinhaltHtml($this->getValue($row, 'content'));
      $hgv->setbemerkungen($this->getValue($row, 'notes'));
      #$hgv->setdaht($this->getValue($row, 'xxx'));
      #$hgv->setldab($this->getValue($row, 'xxx'));
      #$hgv->setdfg($this->getValue($row, 'xxx'));
      $hgv->setddbSer($this->getValue($row, 'ddb_collection_hybrid'));
      $hgv->setddbVol($this->getValue($row, 'ddb_volume_number'));
      $hgv->setddbDoc($this->getValue($row, 'ddb_document_number'));
      #$hgv->setDatensatzNr($this->getValue($row, 'xxx'));
      $hgv->seteingegebenAm(self::makeDate($this->getValue($row, 'created')));
      $hgv->setzulGeaendertAm(self::makeDate($this->getValue($row, 'created')));

      $hgv->setHgvId($hgv->getTmNr() . $hgv->getTexLett());

      if($hgv->getPictureLinks()){
        $hgv->getPictureLinks()->clear();
      } // due to definition »orphanRemoval: true« in »Hgv.orm.yml« old picture links will be deleted (i.e. removed) automatically

      foreach(explode(' ', $this->getValue($row, 'image_link')) as $linkFm){
        $pictureLink = new PictureLink($linkFm);
        $hgv->addPictureLink($pictureLink);
      } // due to definition »cascade: ['persist', 'remove']« in »Hgv.orm.yml« these picture links will become persistent as soon as the owning HGV record will become persistent

      /*
        $cols = $this->xpath->evaluate('fm:COL/fm:DATA[1]', $row);

        if($hgv === null){
          $hgv = new Hgv($cols->item($this->positions['texIdLang'])->nodeValue);
          $hgv->setCreatedAt(new DateTime());
        }
        $hgv->setModifiedAt(new DateTime());

        $hgv->settmNr($cols->item($this->positions['tmNr'])->nodeValue);
        $hgv->settexLett($cols->item($this->positions['texLett'])->nodeValue);
        $hgv->setmehrfachKennung($cols->item($this->positions['mehrfachKennung'])->nodeValue);
        $hgv->setpublikation($cols->item($this->positions['publikation'])->nodeValue);
        $hgv->setband($cols->item($this->positions['band'])->nodeValue);
        $hgv->setzusBand($cols->item($this->positions['zusBand'])->nodeValue);
        $hgv->setnummer($cols->item($this->positions['nummer'])->nodeValue);
        $hgv->setseite($cols->item($this->positions['seite'])->nodeValue);
        $hgv->setzusaetzlich($cols->item($this->positions['zusaetzlich'])->nodeValue);
        $hgv->setpublikationLang($cols->item($this->positions['publikationLang'])->nodeValue);
        $hgv->setmaterial($cols->item($this->positions['material'])->nodeValue);
        $hgv->setzusaetzlichSort($cols->item($this->positions['zusaetzlichSort'])->nodeValue);
        $hgv->setinvNr($cols->item($this->positions['invNr'])->nodeValue);
        $hgv->setjahr($cols->item($this->positions['jahr'])->nodeValue);
        $hgv->setmonat($cols->item($this->positions['monat'])->nodeValue);
        $hgv->settag($cols->item($this->positions['tag'])->nodeValue);
        $hgv->setjh($cols->item($this->positions['jh'])->nodeValue);
        $hgv->seterg($cols->item($this->positions['erg'])->nodeValue);
        $hgv->setjahrIi($cols->item($this->positions['jahrIi'])->nodeValue);
        $hgv->setmonatIi($cols->item($this->positions['monatIi'])->nodeValue);
        $hgv->settagIi($cols->item($this->positions['tagIi'])->nodeValue);
        $hgv->setjhIi($cols->item($this->positions['jhIi'])->nodeValue);
        $hgv->setergIi($cols->item($this->positions['ergIi'])->nodeValue);
        $hgv->setchronMinimum($cols->item($this->positions['chronMinimum'])->nodeValue);
        $hgv->setchronMaximum($cols->item($this->positions['chronMaximum'])->nodeValue);
        $hgv->setchronGlobal($cols->item($this->positions['chronGlobal'])->nodeValue);
        $hgv->seterwaehnteDaten($cols->item($this->positions['erwaehnteDaten'])->nodeValue);
        $hgv->setunsicher($cols->item($this->positions['unsicher'])->nodeValue);
        $hgv->setdatierung($cols->item($this->positions['datierung'])->nodeValue);
        $hgv->setdatierungIi($cols->item($this->positions['datierungIi'])->nodeValue);
        $hgv->setanderePublikation($cols->item($this->positions['anderePublikation'])->nodeValue);
        $hgv->setbl($cols->item($this->positions['bl'])->nodeValue);
        $hgv->setblOnline($cols->item($this->positions['blOnline'])->nodeValue);
        $hgv->setuebersetzungen($cols->item($this->positions['uebersetzungen'])->nodeValue);
        $hgv->setabbildung($cols->item($this->positions['abbildung'])->nodeValue);
        $hgv->setort($cols->item($this->positions['ort'])->nodeValue);
        $hgv->setoriginaltitel($cols->item($this->positions['originaltitel'])->nodeValue);
        $hgv->setoriginaltitelHtml($cols->item($this->positions['originaltitelHtml'])->nodeValue);
        $hgv->setinhalt($cols->item($this->positions['inhalt'])->nodeValue);
        $hgv->setinhaltHtml($cols->item($this->positions['inhaltHtml'])->nodeValue);
        $hgv->setbemerkungen($cols->item($this->positions['bemerkungen'])->nodeValue);
        $hgv->setdaht($cols->item($this->positions['daht'])->nodeValue);
        $hgv->setldab($cols->item($this->positions['ldab'])->nodeValue);
        $hgv->setdfg($cols->item($this->positions['dfg'])->nodeValue);
        $ddbSer = $cols->item($this->positions['ddbSer'])->nodeValue;
        if(!$ddbSer or $ddbSer == ''){
          $ddbSer = $cols->item($this->positions['ddbSerIdp'])->nodeValue;
        }
        $hgv->setddbSer($ddbSer);
        $hgv->setddbVol($cols->item($this->positions['ddbVol'])->nodeValue);
        $hgv->setddbDoc($cols->item($this->positions['ddbDoc'])->nodeValue);
        $hgv->setDatensatzNr($cols->item($this->positions['DatensatzNr'])->nodeValue);

        $hgv->seteingegebenAm(self::makeDate($cols->item($this->positions['eingegebenAm'])->nodeValue));
        $hgv->setzulGeaendertAm(self::makeDate($cols->item($this->positions['zulGeaendertAm'])->nodeValue));

        $hgv->setHgvId($hgv->getTmNr() . $hgv->getTexLett());

        if($hgv->getPictureLinks()){
          $hgv->getPictureLinks()->clear();
        } // due to definition »orphanRemoval: true« in »Hgv.orm.yml« old picture links will be deleted (i.e. removed) automatically

        foreach($this->xpath->evaluate('fm:COL[' . ($this->positions['linkFm'] + 1) . ']/fm:DATA', $row) as $linkFm){
          if($linkFm->nodeValue){
            $pictureLink = new PictureLink($linkFm->nodeValue);
            $hgv->addPictureLink($pictureLink);
          }
        } // due to definition »cascade: ['persist', 'remove']« in »Hgv.orm.yml« these picture links will become persistent as soon as the owning HGV record will become persistent
        */
        return $hgv;
    }
}