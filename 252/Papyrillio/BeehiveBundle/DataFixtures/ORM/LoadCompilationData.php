<?php
namespace Papyrillio\BeehiveBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Papyrillio\BeehiveBundle\Entity\Compilation;
use DateTime;

class LoadCompilationData extends AbstractFixture implements OrderedFixtureInterface
{
    function load(ObjectManager $manager)
    {
        $compilation = null;

        for($i = 1; $i < 14; $i++){
          if($i === 2){
              for($j = 1; $j < 3; $j++){
                $compilation = new Compilation();
                $compilation->setVolume($i);
                $compilation->setFascicle($j);
                $compilation->setStart(new DateTime('2003-01-01'));
                $compilation->setEnd(new DateTime('2007-01-01'));
                $compilation->setPublication(new DateTime('2012-06-06'));
                $manager->persist($compilation);
              }
          } else {
            $compilation = new Compilation();
            $compilation->setVolume($i);
            $compilation->setStart(new DateTime('2003-01-01'));
            $compilation->setEnd(new DateTime('2007-01-01'));
            $compilation->setPublication(new DateTime('2012-06-06'));
    
            $manager->persist($compilation);
          }
          
        }

        $manager->flush();

        $this->addReference('compilation', $compilation); // add last compilation, i.e. 13
    }

    public function getOrder()
    {
        return 2;
    }
}
?>