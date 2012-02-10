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
        $compilation = new Compilation();
        $compilation->setVolume(13);
        $compilation->setStart(new DateTime('2003-01-01'));
        $compilation->setEnd(new DateTime('2007-01-01'));
        $compilation->setPublication(new DateTime('2012-06-06'));

        $manager->persist($compilation);
        $manager->flush();

        $this->addReference('compilation', $compilation);
    }

    public function getOrder()
    {
        return 1;
    }
}
?>