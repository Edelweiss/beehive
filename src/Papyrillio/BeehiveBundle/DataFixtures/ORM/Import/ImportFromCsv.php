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

DEV

cd ~/beehive.dev
php app/console doctrine:fixtures:load --fixtures=src/Papyrillio/BeehiveBundle/DataFixtures/ORM/Import --append

LIVE

cd ~/beehive
cp ../beehive.dev/src/Papyrillio/BeehiveBundle/Resources/data/idno.xml src/Papyrillio/BeehiveBundle/Resources/data/idno.xml
cp ../beehive.dev/src/Papyrillio/BeehiveBundle/Resources/data/import.csv src/Papyrillio/BeehiveBundle/Resources/data/import.csv
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
       $row = 1;
       if(($handle = fopen(self::IMPORT_FILE, 'r')) !== FALSE){
         while(($data = fgetcsv($handle, 1000, ',')) !== FALSE){
           $hgv = preg_replace('/[^\da-z]+.*$/', '', $data[self::CSV_HGV]);
           if(preg_match('/\d+([a-z]+)?/', $hgv)){
             $compilationTitle = $data[self::CSV_COMPILATION_TITLE];
             $compilationPage  = $data[self::CSV_COMPILATION_PAGE];
             $tm               = preg_replace('/[a-z]+/', '', $hgv);
             $folder           = ceil($tm / 1000);
             $text1            = $data[self::CSV_TEXT1];
             $text2            = $data[self::CSV_TEXT2];
             $position         = $data[self::CSV_POSITION];
             $description      = $data[self::CSV_DESCRIPTION];
             $editionSort      = $data[self::CSV_EDITION_SORT];
             $source           = is_numeric($data[self::CSV_SOURCE]) ? $data[self::CSV_SOURCE] : null;
             $creator          = isset($data[self::CSV_CREATOR]) && strlen($data[self::CSV_CREATOR]) ? $data[self::CSV_CREATOR] : self::DEFAULT_CREATOR;
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

             echo $row . '> [Edition #' . $correction->getEdition()->getId() . ']' . ' [Compilation #' . $correction->getCompilation()->getId() . '] ' . $correction->getHgv() . ' (' . $correction->getFolder() .  ') ' . $correction->getCollection() . ';' . $correction->getVolume() . ';' . $correction->getDocument() . "\n";
             echo $row . '> Text: ' . $correction->getText() . "\n\n";

             //$manager->persist($correction);

             $row++;
           }
       }
       //$manager->flush();
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
      } else {
        throw Exception;
      }

      $ddbExploded = explode(';', $ddb);
      return array('ddb' => $ddb, 'collection' => $ddbExploded[0], 'volume' => $ddbExploded[1], 'document' => $ddbExploded[2]);
    }

    protected function formatText($text1, $text2, $editionSort){
      $editionSort = $editionSort * 1;
      if(!isset($text2) || !strlen($text2)){ // just one piece of information
        return $text1;
      }elseif($editionSort === 1250000){ // (S. 124) 292
        return '(' . $text1 . ') ' . $text2;
      }elseif(in_array($editionSort, array(580000, 581000, 582000))){ // 1164 h (S. 163)
        return $text2 . ' (' . $text1 . ') ';
      }
      return $text1 . ' ' . $text2;
    }

    protected function getCompilation($title, $manager){
      if(!isset($this->compilationList[$title])){
        $compilation = $manager->getRepository('PapyrillioBeehiveBundle:Compilation')->findOneBy(array('title' => $title));
        $this->compilationList[$title] = $compilation;
      }
      return $this->compilationList[$title];
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

}
?>