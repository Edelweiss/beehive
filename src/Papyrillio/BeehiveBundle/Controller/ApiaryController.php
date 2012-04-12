<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\BeehiveBundle\Entity\Correction;
use Papyrillio\BeehiveBundle\Entity\Compilation;
use DateTime;

class ApiaryController extends BeehiveController{
  
  protected static $TYPES = array('tm' => 'c.tm', 'hgv' => 'c.hgv', 'ddb' => 'c.ddb', 'biblio' => 'c.source', 'bl' => 'c2.volume');
  
  public function indexAction(){
    return $this->render('PapyrillioBeehiveBundle:Apiary:index.html.twig');
  }

  public function honeyAction($type, $id, $format = 'html'){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');

    $query = $entityManager->createQuery('
      SELECT e, c, t FROM PapyrillioBeehiveBundle:Correction c
      LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2 WHERE c.status = :status AND ' . self::$TYPES[$type] . ' = :id ORDER BY c.sort'
    );
    $query->setParameters(array('status' => 'finalised', 'id' => $id));

    $corrections = $query->getResult();

    return $this->render('PapyrillioBeehiveBundle:Apiary:honey.html.twig', array('corrections' => $corrections));
  }

}
