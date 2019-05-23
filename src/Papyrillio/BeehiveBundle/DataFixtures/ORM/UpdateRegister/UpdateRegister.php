<?php
namespace Papyrillio\BeehiveBundle\DataFixtures\ORM;
ini_set('memory_limit', '4096M');
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
php app/console doctrine:fixtures:load --fixtures=src/Papyrillio/BeehiveBundle/DataFixtures/ORM/UpdateRegister --append

LIVE

cd ~/beehive
php app/console doctrine:fixtures:load --fixtures=src/Papyrillio/BeehiveBundle/DataFixtures/ORM/UpdateRegister --append

*/

class UpdateRegister extends AbstractFixture implements OrderedFixtureInterface
{
    const IDNO_FILE   = 'src/Papyrillio/BeehiveBundle/Resources/data/idno.xml';
    protected static $idnoXpath = null;

    function load(ObjectManager $manager)
    {
      echo date('l jS \of F Y h:i:s A') . "\n";
      echo '----  updateRegisterFromEpiDocIdnos ----' . "\n";
      $this->updateRegisterFromEpiDocIdnos($manager);
      echo '--' . "\n";

      echo date('l jS \of F Y h:i:s A') . "\n";
      echo '----  updateRegisterFromEpiDocIdnos ----' . "\n";
      $this->checkRegister($manager);
      echo '--' . "\n";

      echo date('l jS \of F Y h:i:s A') . "\n";
    }

    protected function updateRegisterFromEpiDocIdnos($manager){
      $itemList = self::getIdnoXpath()->evaluate('/list/item');
        for($i = 0; $i < $itemList->length; $i++){
          $hgvIdno = self::getIdnoXpath()->query("idno[@type='hgv']", $itemList->item($i));
          $ddbIdno = self::getIdnoXpath()->query("idno[@type='ddb']", $itemList->item($i));
          $tmIdno  = self::getIdnoXpath()->query("idno[@type='tm']", $itemList->item($i));
          $hgvIdno = $hgvIdno->length ? $hgvIdno->item(0)->nodeValue : null;
          $ddbIdno = $ddbIdno->length ? $ddbIdno->item(0)->nodeValue : null;
          $tmIdno  = $tmIdno->length  ? $tmIdno->item(0)->nodeValue  : null;

          // DDB split

          if(preg_replace('/[a-z]+/', '', $hgvIdno) == $tmIdno)
          {
            if(!preg_match('/^sosol;/', $ddbIdno)){
              echo ($hgvIdno ? $hgvIdno : '') . ($tmIdno ? '/' . $tmIdno : '') . ($ddbIdno ? ' ' . $ddbIdno : '') . '> ';
              $query = $manager->createQuery('SELECT r.id FROM PapyrillioBeehiveBundle:Register r ' . ' WHERE r.hgv = ' . "'" . $hgvIdno . "'");
              $selected = $query->getResult();
              if(count($selected)){ // UPDATE EXISTING HGV (HGV number are unique, in EpiDoc: tm = hgv - [a-z])
                $query = $manager->createQuery('UPDATE PapyrillioBeehiveBundle:Register r SET r.tm = ' . "'" . $tmIdno . "'" . ',  r.ddb = ' . "'" . $ddbIdno . "'" . ' WHERE r.hgv = ' . "'" . $hgvIdno . "'");
                $updated = $query->getResult();
                echo $updated . ' item' . ($updated != 1 ? 's' : '' ) . ' updated' . "\n";
              } else {
                $query = $manager->createQuery('SELECT r.id FROM PapyrillioBeehiveBundle:Register r ' . ' WHERE r.tm = ' . "'" . $tmIdno . "'");
                $selected = $query->getResult();
                if(count($selected)){
                  // UPDATE EXISTING TM
                  $query = $manager->createQuery('UPDATE PapyrillioBeehiveBundle:Register r SET r.hgv = ' . "'" . $hgvIdno . "'" . ',  r.ddb = ' . "'" . $ddbIdno . "'" . ' WHERE r.tm = ' . "'" . $tmIdno . "'");
                  $updated = $query->getResult();
                  echo $updated . ' item' . ($updated != 1 ? 's' : '' ) . ' updated' . "\n";
                } else { // INSERT COMPLETELY NEW
                  $register = new Register();
                  $register->setTm($tmIdno);
                  $register->setHgv($hgvIdno);
                  $register->setDdb($ddbIdno);
                  $manager->persist($register);
                  $manager->flush();
                  echo 'new register entry' . "\n";
                }
              }
            } else {
              echo 'ACHTUNG, DDB-hybrid!!! ' . ($hgvIdno ? $hgvIdno : '') . ($tmIdno ? '/' . $tmIdno : '') . ($ddbIdno ? ' ' . $ddbIdno : '') . "\n";
              //throw new Exception('Keine DDB-Hybrid');
            }
          } else {
            throw new Exception('TM-Nummer weicht von HGV-Nummer ab');
          }
        }
    }

    protected function checkRegister($manager){
      $repository = $manager->getRepository('PapyrillioBeehiveBundle:Register');

      // HGV ids müssen unique sein
      // ALTER TABLE register ADD CONSTRAINT register_hgv_unique UNIQUE (hgv);

      // jedes Dreitupel aus hgv, tm und ddb muss unique sein
      // ALTER TABLE register ADD CONSTRAINT register_hgv_tm_ddb_unique UNIQUE (hgv, tm, ddb);

      // jede HGV-Nummer muss im idno file vorhanden sein
      $hgvIdnoList = self::getIdnoXpath()->evaluate("//idno[@type='hgv']");

      $hgvIdnoLookup = array();
      for($i = 0; $i < $hgvIdnoList->length; $i++){
        $hgvIdnoLookup[] =  $hgvIdnoList->item($i)->nodeValue;
      }

      $query = $manager->createQuery('SELECT r.id, r.hgv, r.tm, r.ddb FROM PapyrillioBeehiveBundle:Register r ORDER by r.id');
      foreach($query->getResult() as $result){
        if(!in_array($result['hgv'], $hgvIdnoLookup)){
          echo 'ACHTUNG, ungültige HGV-Nummer!!! (' . $result['hgv'] . ')' . "\n";
        } else {
          echo 'alles okay' . "\n";
        }
      }

      // cl: jede TM-Nummer muss über texrelations abrufbar sein
    }

    protected static function findOrCreateRegisterByHgvOrTm($hgvOrTm){
      if($register = $registerRepository->findOneBy(array('hgv' => $hgvOrTm))){
        return $register;
      } // HGV numbers are unique
      if($register = $registerRepository->findOneBy(array('tm' => $hgvOrTm))){
        return $register;
      } // If the number can’t be found in HGV try TM

      // TM number needs to be added to the register
      // cl: check https://www.trismegistos.org/dataservices/texrelations/xml/9831
      $register = new Register();
      if($hgvOrTm && strlen($hgvOrTm)){
        $register->setTm($hgvOrTm);
      }
      $this->getDoctrine()->getEntityManager()->persist($register);
      //$this->getDoctrine()->getEntityManager()->flush();
      return $register;
    }

    static function getIdnoXpath(){
      if(!self::$idnoXpath){
        $doc = new DOMDocument();
        $doc->load(self::IDNO_FILE);
        self::$idnoXpath = new DOMXPath($doc);
      }
      return self::$idnoXpath;
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

    public function getOrder()
    {
        return 1;
    }
}
?>