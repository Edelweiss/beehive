<?php
namespace Papyrillio\BeehiveBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Papyrillio\BeehiveBundle\Entity\Compilation;
use Papyrillio\BeehiveBundle\Entity\Correction;
use Papyrillio\BeehiveBundle\Entity\Task;
use Papyrillio\BeehiveBundle\Entity\Register;
use DateTime;
use DOMDocument;
use DOMXPath;
use Exception;

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
    const TEXT_PASSIM = 'passim';
    const TEXT_EMPTY = 'XXX';

    protected $editionList = array();
    protected $compilationList = array();
    protected $editionExampleCorrection = array();

    protected static $idnoXpath = null;
    protected static $hgvDdb = array();

    static function fallback($value, $fallback){
      if(!isset($value) || $value === null || $value === '')
      {
        return $fallback;
      }
      return $value;
    }

    function load(ObjectManager $manager)
    {
       $row = 0;

       $editionSortFallback = '';
       $hgvFallback = '';
       $text1Fallback = '';
       $text2Fallback = '';

       if(($handle = fopen(self::IMPORT_FILE, 'r')) !== FALSE){
         while(($data = fgetcsv($handle, 0, ',')) !== FALSE){
           $row++;
           $editionSort = $data[self::CSV_EDITION_SORT];
            // cl$hgv         = preg_replace('/[^\da-z]+.*$/', '', $data[self::CSV_HGV]);
           $hgv = $data[self::CSV_HGV];
           if(preg_match('/^\d+([a-z]+)?([^\da-z]+\d+([a-z]+)?)+$/', $hgv)){
             $hgv = preg_split('/[^\da-z]+/', $hgv, null, PREG_SPLIT_NO_EMPTY);
           } elseif(preg_match('/^\d+([a-z]+)?$/', $hgv)) {
             $hgv = array($hgv);
           } else {
             $hgv = null;
           }
           $text1       = $data[self::CSV_TEXT1];
           $text2       = $data[self::CSV_TEXT2];

           if(strlen($editionSort) && !$hgv){ // Überschriftzeile gefunden
             echo $row . ' -------- ' . $data[self::CSV_DESCRIPTION] . "\n";
             $editionSortFallback = $editionSort;
             $hgvFallback         = '';
             $text1Fallback       = '';
             $text2Fallback       = '';
           } else { // Datenzeile gefunden
             $editionSort = self::fallback($editionSort, $editionSortFallback);
             $hgv         = self::fallback($hgv, $hgvFallback);
             $text1       = self::fallback($text1, $text1Fallback);
             $text2       = self::fallback($text2, $text2Fallback);

             $hgvFallback = $hgv;
             $text1Fallback = $text1;
             $text2Fallback = $text2;

             echo $row . ' Ed. ' . $editionSort;

             if(($hgv && strlen($text1)) || $text1 == self::TEXT_PASSIM){
               $compilationTitle = $data[self::CSV_COMPILATION_TITLE];
               $compilationPage  = $data[self::CSV_COMPILATION_PAGE];
               $description      = $data[self::CSV_DESCRIPTION];
               $source           = is_numeric($data[self::CSV_SOURCE]) ? $data[self::CSV_SOURCE] : null;
               $creator          = isset($data[self::CSV_CREATOR]) && strlen($data[self::CSV_CREATOR]) ? $data[self::CSV_CREATOR] : self::DEFAULT_CREATOR;
               $status           = self::DEFAULT_STATUS;

               $correction = new Correction();
               $correction->setEdition($this->getEdition($editionSort, $manager));
               $correction->setCompilation($this->getCompilation($compilationTitle, $manager));
               $correction->setText($this->formatText($text1, $text2, $editionSort));

               if($correction->getText() != self::TEXT_PASSIM){
                 foreach($hgv as $hgvOrTm){
                   $correction->addRegisterEntry(self::findOrCreateRegisterByHgvOrTm($manager, $hgvOrTm));
                 }

                 echo "\t";
                 foreach($correction->getRegisterEntries() as $register){
                   echo '[' . $register . ']';
                 }

                 $correction->setPosition($data[self::CSV_POSITION]);
               }
               $correction->setDescription($description);
               $correction->setSource($source);
               $correction->setStatus($status);
               $correction->setCreator($creator);
               $correction->setCompilationPage($compilationPage);

               echo "\n";
               $manager->persist($correction);
             } else {
               echo "\n";
               throw new Exception('ungültige Zeile gefunden.');
             }
           }
           if(($row + 0) % 400 === 0){
             $manager->flush();
           }
       }
       $manager->flush();
       fclose($handle);
      }
    }

    static $NEW_REGISTER_ENTRIES = array();

    protected static function findOrCreateRegisterByHgvOrTm($manager, $hgvOrTm){
      if(isset(self::$NEW_REGISTER_ENTRIES[$hgvOrTm])){
        return self::$NEW_REGISTER_ENTRIES[$hgvOrTm];
      } // check whether the number will be added to the register with just this current import
      if($register = $manager->getRepository('PapyrillioBeehiveBundle:Register')->findOneBy(array('hgv' => $hgvOrTm))){
        return $register;
      } // HGV numbers are unique
      if($register = $manager->getRepository('PapyrillioBeehiveBundle:Register')->findOneBy(array('tm' => $hgvOrTm))){
        return $register;
      } // If the number can’t be found in HGV try TM

      // TM number needs to be added to the register
      // cl: check against https://www.trismegistos.org/dataservices/texrelations/xml/9831
      $register = new Register();
      if($hgvOrTm && strlen($hgvOrTm)){
        $register->setTm($hgvOrTm);
      }
      $manager->persist($register);
      //$manager->flush();
      self::$NEW_REGISTER_ENTRIES[$hgvOrTm] = $register;
      return $register;
    }

    public function getOrder()
    {
        return 1;
    }

    static function getIdnoXpath(){
      if(!self::$idnoXpath){
        $doc = new DOMDocument();
        $doc->load(self::IDNO_FILE);
        self::$idnoXpath = new DOMXPath($doc);
      }
      return self::$idnoXpath;
    }

    protected function checkHgv($hgv){
      $lookupList = self::getHgvDdb();
      return isset($lookupList[$hgv]) ? true : false;
    }

    protected static function getHgvDdb(){
      if(!count(self::$hgvDdb)){
        //$xpath->registerNamespace('fm', self::NAMESPACE_FILEMAKER);
        $itemList = self::getIdnoXpath()->evaluate('/list/item');
        for($i = 0; $i < $itemList->length; $i++){
          $hgvIdno = self::getIdnoXpath()->query("idno[@type='hgv']", $itemList->item($i));
          $ddbIdno = self::getIdnoXpath()->query("idno[@type='ddb']", $itemList->item($i));
          $hgvIdno = $hgvIdno->length ? $hgvIdno->item(0)->nodeValue : null;
          $ddbIdno = $ddbIdno->length ? $ddbIdno->item(0)->nodeValue : null;
          if($hgvIdno){
            if($ddbIdno){
              $ddbExploded = explode(';', $ddbIdno);
              $ddbIdno = array('ddb' => $ddbIdno, 'collection' => $ddbExploded[0], 'volume' => $ddbExploded[1], 'document' => $ddbExploded[2]);
            }
            self::$hgvDdb[$hgvIdno] = $ddbIdno;
          } else {
            throw new Exception('invalid item found in xml (hgv: ' . ($hgvIdno ? $hgvIdno : 'null') . '; ddb: ' . ($ddbIdno ? $ddbIdno : 'null') . ')');
          }
        }
      }
      return self::$hgvDdb;
    }

    protected function getDdb($hgv){
      $lookupList = self::getHgvDdb();
      return isset($lookupList[$hgv]) ? $lookupList[$hgv] : null;
    }

    protected function formatText($text1, $text2, $editionSort){
      $editionSort = $editionSort * 1;
      if($text1 == self::TEXT_EMPTY){ // Text was flagged to be empty
        return '';
      } elseif(!isset($text2) || !strlen($text2)){ // just one piece of information
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