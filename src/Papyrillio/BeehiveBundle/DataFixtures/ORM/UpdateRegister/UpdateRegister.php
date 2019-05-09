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
php app/console doctrine:fixtures:load --fixtures=src/Papyrillio/BeehiveBundle/DataFixtures/ORM/UpdateRegister --append

LIVE

cd ~/beehive
php app/console doctrine:fixtures:load --fixtures=src/Papyrillio/BeehiveBundle/DataFixtures/ORM/UpdateRegister --append

*/

class UpdateRegister extends AbstractFixture implements OrderedFixtureInterface
{
    function load(ObjectManager $manager)
    {
      $repository = $manager->getRepository('PapyrillioBeehiveBundle:Register');

      $query = $manager->createQuery('SELECT r FROM PapyrillioBeehiveBundle:Register r ORDER by r.ddb');
      foreach($query->getResult() as $result){
        echo $result . "\n";
      }

      // alles aus HGV übertragen
      
      // alles mit HGV abgleichen
      
      // auf Dubletten überprüfen und ausmisten
      
      // Ungereimtheiten melden
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

    public function getOrder()
    {
        return 1;
    }
}
?>