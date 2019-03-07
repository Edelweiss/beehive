<?php
namespace Papyrillio\BeehiveBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Papyrillio\BeehiveBundle\Entity\Compilation;
use Papyrillio\BeehiveBundle\Entity\Correction;
use Papyrillio\BeehiveBundle\Entity\Task;
use DateTime;

/*

php app/console doctrine:fixtures:load --fixtures=src/Papyrillio/BeehiveBundle/DataFixtures/ORM/Import --append

*/

class ImportFromCsv extends AbstractFixture implements OrderedFixtureInterface
{
    const CSV_COMPILATION_TITLE = 0;
    const CSV_COMPILATION_PAGE  = 1;
    const CSV_TM                = 2;
    const CSV_TEXT1             = 3;
    const CSV_TEXT2             = 4;
    const CSV_POSITION          = 5;
    const CSV_DESCRIPTION       = 6;
    const CSV_EDITION_SORT      = 7;
    const CSV_SOURCE            = 8;
    const CSV_CREATOR           = 9;

    const IMPORT_FILE = 'src/Papyrillio/BeehiveBundle/Resources/data/import.csv';
    
    const DEFAULT_STATUS = 'unchecked';
    const DEFAULT_CREATOR = 'system';

    protected $editionList = array();
    protected $compilationList = array();
    protected $editionExampleCorrection = array();

    function load(ObjectManager $manager)
    {
       //$csv = file_get_contents('src/Papyrillio/BeehiveBundle/Resources/data/import.csv');
       //echo $csv;

       $row = 1;
       if(($handle = fopen(self::IMPORT_FILE, 'r')) !== FALSE){
         while(($data = fgetcsv($handle, 1000, ',')) !== FALSE){
           $compilationTitle = $data[self::CSV_COMPILATION_TITLE];
           $compilationPage  = $data[self::CSV_COMPILATION_PAGE];
           $tm               = $data[self::CSV_TM];
           $folder           = ceil($tm / 1000);
           $hgv              = $tm;
           $text1            = $data[self::CSV_TEXT1];
           $text2            = $data[self::CSV_TEXT2];
           $position         = $data[self::CSV_POSITION];
           $description      = $data[self::CSV_DESCRIPTION];
           $editionSort      = $data[self::CSV_EDITION_SORT];
           $source           = $data[self::CSV_SOURCE];
           $creator          = self::DEFAULT_CREATOR;
           $status           = self::DEFAULT_STATUS;

           $edition = $this->getEdition($editionSort, $manager);
           $editionX = $this->getEditionExampleCorrection($editionSort, $manager);           
           $editionId = $editionX->getEdition()->getId();


           $correction = new Correction();
           $correction->setEdition($editionX->getEdition());
           $correction->setCompilation($this->getCompilation($compilationTitle, $manager));
           $correction->setText($this->formatText($text1, $text2, $editionSort));
           $correction->setDdb($editionX->getDdb());
           $correction->setCollection($editionX->getCollection());
           $correction->setVolume($editionX->getVolume());
           $correction->setDocument($editionX->getDocument());
           

           //echo $row . '> '. $compilation . '|' . $compilationPage . '|' . $tm . '|' . $text . '|' . $text2 . '|' . $position . '|' . $description . '|' . $editionSort . '|' . $source . '|' . $creator . "\n";
           echo $row . '> Edition: ' . $correction->getEdition()->getId() . ' DDB: ' . $correction->getDdb() . ' (' . $correction->getCollection() . '|' . $correction->getVolume() . '|' . $correction->getDocument() . ")\n";
           echo $row . '> Compilation: ' . $correction->getCompilation()->getId() . ' ' . $correction->getCompilation()->getTitle() . "\n";
           echo $row . '> TM: ' . $tm . ' (' . $folder . ")\n";
           echo $row . '> Text: ' . $correction->getText() . "\n";
           echo $row . "> --\n";

           $row++;

       }
       fclose($handle);
      }
    }

    public function getOrder()
    {
        return 1;
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