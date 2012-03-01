<?php
namespace Papyrillio\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Papyrillio\UserBundle\Entity\User;

class LoadUserData implements FixtureInterface, ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    function load(ObjectManager $manager)
    {
        $dataList = array(
            array('r' => array('ROLE_USER', 'ROLE_ADMIN'), 'u' => 'lanz',       'n' => 'Carmen Lanz',       'e' => 'carmen_lanz@ossiriand.de'),
            array('r' => array('ROLE_USER', 'ROLE_ADMIN'), 'u' => 'cowey',      'n' => 'James Cowey',       'e' => 'james.cowey@urz.uni-heidelberg.de'),
            array('r' => array('ROLE_USER'),            'u' => 'jördens',    'n' => 'Andrea Jördens',    'e' => 'andrea.joerdens@zaw.uni-heidelberg.de'),
            array('r' => array('ROLE_USER'),            'u' => 'ast',        'n' => 'Rodney Ast',        'e' => 'rodney.ast@zaw.uni-heidelberg.de'),
            array('r' => array('ROLE_USER'),            'u' => 'hoogendijk', 'n' => 'Cisca Hoogendijk', 'e' => 'f_a_j_hoogendijk@library.leidenuniv.nl')
        );

        foreach($dataList as $data){
          $user = new User();
          $user->setUsername($data['u']);
          $user->setEmail($data['e']);
          $user->setName($data['n']);
          $user->setRoles($data['r']);
          
          $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
          $user->setPassword($encoder->encodePassword('secret', $user->getSalt()));

          $manager->persist($user);
        }

        $manager->flush();
    }
}
?>