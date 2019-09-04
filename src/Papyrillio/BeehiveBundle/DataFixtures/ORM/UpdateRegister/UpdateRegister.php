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
      echo '----  checkRegister ----' . "\n";
      $this->checkRegister($manager);
      echo '--' . "\n";

      echo date('l jS \of F Y h:i:s A') . "\n";
    }

    protected function updateRegisterFromEpiDocIdnos($manager){
      $itemList = self::getIdnoXpath()->evaluate('/list/item');
        for($i = 0; $i < $itemList->length; $i++){
          // get idnos from EpiDoc
          $hgvIdno  = self::getIdnoXpath()->query("idno[@type='hgv']", $itemList->item($i));
          $ddbIdno  = self::getIdnoXpath()->query("idno[@type='ddb']", $itemList->item($i));
          $dclpIdno = self::getIdnoXpath()->query("idno[@type='dclp']", $itemList->item($i));
          $tmIdno   = self::getIdnoXpath()->query("idno[@type='tm']", $itemList->item($i));

          $hgvIdno  = $hgvIdno->length  ? $hgvIdno->item(0)->nodeValue  : null;
          $ddbIdno  = $ddbIdno->length  ? $ddbIdno->item(0)->nodeValue  : null;
          $dclpIdno = $dclpIdno->length ? $dclpIdno->item(0)->nodeValue : null;
          $tmIdno   = $tmIdno->length   ? $tmIdno->item(0)->nodeValue   : null;

          $sethgv  = ' r.hgv = ' . "'" . $hgvIdno . "'";
          $setTm   = ' r.tm = ' . "'" . $tmIdno . "'";
          $setDdb  = $ddbIdno  ? ',  r.ddb = ' . "'" . $ddbIdno . "'"   : '';
          $setDclp = $dclpIdno ? ',  r.dclp = ' . "'" . $dclpIdno . "'" : '';

          $hgvWithoutTexlett = preg_replace('/[a-z]+/', '', $hgvIdno) + 0;

          if(preg_match('/^\d+[a-z]*$/', $hgvIdno) && (($hgvWithoutTexlett < 500000)) || ($hgvWithoutTexlett >= 501000)) {
            if($hgvWithoutTexlett == $tmIdno) {
              if(!preg_match('/^sosol;/', $ddbIdno)){
                $idnoInfo = $hgvIdno . '/' .$tmIdno . '/' . ($ddbIdno ?  $ddbIdno : 'NO DDB') . '/' . ($dclpIdno ?  $dclpIdno : 'NO DCLP') . '>';
                $query = $manager->createQuery('SELECT r.id FROM PapyrillioBeehiveBundle:Register r ' . ' WHERE r.hgv = ' . "'" . $hgvIdno . "'");
                $selected = $query->getResult();
                if(count($selected) < 2){
                  if(count($selected) === 1){ // UPDATE EXISTING HGV (HGV numbers are unique, in EpiDoc: tm = hgv - [a-z])
                    $query = $manager->createQuery('UPDATE PapyrillioBeehiveBundle:Register r SET ' . $setTm . $setDdb . $setDclp . ' WHERE r.hgv = ' . "'" . $hgvIdno . "'");
                    $updated = $query->getResult();
                    if($updated){
                      echo $idnoInfo . ' updated (HGV)' . "\n";
                    }
                  } else {
                    $query = $manager->createQuery('SELECT r.id FROM PapyrillioBeehiveBundle:Register r ' . ' WHERE r.tm = ' . "'" . $tmIdno . "'");
                    $selected = $query->getResult();
                    if(count($selected)){ // UPDATE EXISTING TM
                      $query = $manager->createQuery('UPDATE PapyrillioBeehiveBundle:Register r SET ' . $sethgv . $setDdb . $setDclp . ' WHERE r.tm = ' . "'" . $tmIdno . "'");
                      $updated = $query->getResult();
                      if($updated){
                        echo $idnoInfo . ' updated (TM)' . "\n";
                      }
                    } else { // INSERT COMPLETELY NEW
                      $register = new Register();
                      $register->setTm($tmIdno);
                      $register->setHgv($hgvIdno);
                      $register->setDdb($ddbIdno);
                      $register->setDdb($dclpIdno);
                      $manager->persist($register);
                      $manager->flush();
                      echo $idnoInfo . 'new register entry' . "\n";
                    }
                  }
                } else {
                  echo 'ACHTUNG: TM-Nummer in Datenbank nicht unique (' . $hgvIdno . ')' . "\n";
                }
              } else {
                echo 'ACHTUNG: SoSOL-DDB-hybrid ' . ($hgvIdno ? $hgvIdno : '') . ($tmIdno ? '/' . $tmIdno : '') . ($ddbIdno ? ' ' . $ddbIdno : '') . "\n";
              }
            } else {
              echo 'ACHTUNG: TM-Nummer weicht von HGV-Nummer ab (' . $hgvIdno . '/' . $tmIdno . ')' . "\n";
            }
          } else {
            echo 'ACHTUNG: ungültige HGV-Nummer (' . $hgvIdno . ')' . "\n";
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
        if(!empty($result['hgv']) && !in_array($result['hgv'], $hgvIdnoLookup)){
          echo 'ACHTUNG: ungültige HGV-Nummer (' . $result['hgv'] . ')' . "\n";
        }
      } // cl: dieser Teil ist ausbaufähig zum Beispiel für die Fälle, wo eine HGV-Nummer 1830 zu 1830a und 1830b wurde (automatische Löschung?!)

      // cl: jede TM-Nummer muss über texrelations abrufbar sein. Nachtrag: leider nein, weil textrelations nicht vollständig ist, vgl. https://tristmegistos/texte/<TMNUMMER>
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

    public function getOrder()
    {
        return 1;
    }
}
?>