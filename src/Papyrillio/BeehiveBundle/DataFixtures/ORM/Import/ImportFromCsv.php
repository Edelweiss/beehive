<?php
namespace Papyrillio\BeehiveBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Papyrillio\BeehiveBundle\Entity\Compilation;
use Papyrillio\BeehiveBundle\Entity\Correction;
use Papyrillio\BeehiveBundle\Entity\Task;
use DateTime;
use DOMDocument;
use DOMXPath;

/*

php app/console doctrine:fixtures:load --fixtures=src/Papyrillio/BeehiveBundle/DataFixtures/ORM/Import --append

*/

class ImportFromCsv extends AbstractFixture implements OrderedFixtureInterface
{
    const CSV_COMPILATION_TITLE = 0;
    const CSV_COMPILATION_PAGE  = 1;
    const CSV_HGV               = 2;
    const CSV_TEXT1             = 3;
    const CSV_TEXT2             = 4;
    const CSV_POSITION          = 5;
    const CSV_DESCRIPTION       = 6;
    const CSV_EDITION_SORT      = 7;
    const CSV_SOURCE            = 8;
    const CSV_CREATOR           = 9;

    const IMPORT_FILE = 'src/Papyrillio/BeehiveBundle/Resources/data/import.csv';
    const IDNO_FILE   = 'src/Papyrillio/BeehiveBundle/Resources/data/idno.xml';
    
    const DEFAULT_STATUS = 'unchecked';
    const DEFAULT_CREATOR = 'system';

    protected $editionList = array();
    protected $compilationList = array();
    protected $editionExampleCorrection = array();
    
    protected static $idnoXpath = null;

    function load(ObjectManager $manager)
    {
       //$csv = file_get_contents('src/Papyrillio/BeehiveBundle/Resources/data/import.csv');
       //echo $csv;

       $row = 1;
       if(($handle = fopen(self::IMPORT_FILE, 'r')) !== FALSE){
         while(($data = fgetcsv($handle, 1000, ',')) !== FALSE){
           $compilationTitle = $data[self::CSV_COMPILATION_TITLE];
           $compilationPage  = $data[self::CSV_COMPILATION_PAGE];
           $hgv              = $data[self::CSV_HGV];
           $tm               = preg_replace('/[a-z]+/', '', $hgv);
           $folder           = ceil($tm / 1000);
           $text1            = $data[self::CSV_TEXT1];
           $text2            = $data[self::CSV_TEXT2];
           $position         = $data[self::CSV_POSITION];
           $description      = $data[self::CSV_DESCRIPTION];
           $editionSort      = $data[self::CSV_EDITION_SORT];
           $source           = is_numeric($data[self::CSV_SOURCE]) ? $data[self::CSV_SOURCE] : null;
           $creator          = $data[self::CSV_CREATOR];
           $status           = self::DEFAULT_STATUS;

           $ddb = $this->getDdb($hgv);

           $correction = new Correction();
           $correction->setEdition($this->getEdition($editionSort, $manager));
           $correction->setCompilation($this->getCompilation($compilationTitle, $manager));
           $correction->setText($this->formatText($text1, $text2, $editionSort));
           $correction->setDdb($ddb['ddb']);
           $correction->setCollection($ddb['collection']);
           $correction->setVolume($ddb['volume']);
           $correction->setDocument($ddb['document']);
           $correction->setDescription($description);
           $correction->setTm($tm);
           $correction->setHgv($hgv);
           $correction->setFolder($folder);
           $correction->setPosition($position);
           $correction->setSource($source);
           $correction->setStatus($status);
           $correction->setCreator($creator);
           $correction->setCompilationPage($compilationPage);
           

           //echo $row . '> '. $compilation . '|' . $compilationPage . '|' . $tm . '|' . $text . '|' . $text2 . '|' . $position . '|' . $description . '|' . $editionSort . '|' . $source . '|' . $creator . "\n";
           echo $row . '> Edition: ' . $correction->getEdition()->getId() . ' DDB: ' . $correction->getDdb() . ' (' . $correction->getCollection() . '|' . $correction->getVolume() . '|' . $correction->getDocument() . ")\n";
           echo $row . '> Compilation: ' . $correction->getCompilation()->getId() . ' ' . $correction->getCompilation()->getTitle() . "\n";
           echo $row . '> HGV/TM: ' . $hgv . '/' . $tm . ' (' . $folder . ")\n";
           echo $row . '> Text: ' . $correction->getText() . "\n";
           echo $row . "> --\n";

           $manager->persist($correction);

           $row++;

       }
       $manager->flush();
       fclose($handle);
      }
    }

    public function getOrder()
    {
        return 1;
    }
    
    protected function getDdb($hgv){
      if(!self::$idnoXpath){
        $doc = new DOMDocument();
        $doc->load(self::IDNO_FILE);
        self::$idnoXpath = new DOMXPath($doc);
      }

      //$xpath->registerNamespace('fm', self::NAMESPACE_FILEMAKER);
      
      $ddb = ';;';
      $ddbIdno = self::$idnoXpath->evaluate("/list/item[idno[@type='hgv'][string(.)='" . $hgv . "']]/idno[@type='ddb']");

      if($ddbIdno->length > 0){
        $ddb = $ddbIdno->item(0)->nodeValue;
      }

      $ddbExploded = explode(';', $ddb);
      return array('ddb' => $ddb, 'collection' => $ddbExploded[0], 'volume' => $ddbExploded[1], 'document' => $ddbExploded[2]);
    }

    protected function formatText($text1, $text2, $editionSort){
      if($editionSort * 1 === 1250000){ // (S. 124) 292
        return '(' . $text1 . ') ' . $text2;
      }
      return $text1 . ' ' . $text2;
    }

    protected function getEdition($sort, $manager){
      if(!isset($this->editionList[$sort])){
        $edition = $manager->getRepository('PapyrillioBeehiveBundle:Edition')->findOneBy(array('sort' => $sort));
        $this->editionList[$sort] = $edition;
      }
      return $this->editionList[$sort];
    }

    protected function getEditionExampleCorrection($sort, $manager){
      if(!isset($this->editionExampleCorrection[$sort])){
        $repository = $manager->getRepository('PapyrillioBeehiveBundle:Correction');

        $query = $manager->createQuery('
          SELECT c2, e, c FROM PapyrillioBeehiveBundle:Correction c JOIN c.edition e JOIN c.compilation c2 WHERE e.sort = :sort'
        );

        $query->setParameters(array('sort' => $sort));
        $query->setMaxResults(1);  
        $corrections = $query->getResult();

        $this->editionExampleCorrection[$sort] = $corrections[0];
      }
      return $this->editionExampleCorrection[$sort];
    }

    protected function getCompilation($title, $manager){
      if(!isset($this->compilationList[$title])){
        $compilation = $manager->getRepository('PapyrillioBeehiveBundle:Compilation')->findOneBy(array('title' => $title));
        $this->compilationList[$title] = $compilation;
      }
      return $this->compilationList[$title];
    }

}
?>