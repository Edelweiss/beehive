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
    
    // WHERE
    
    $where = self::$TYPES[$type] . ' = :id';
    $parameters = array('id' => $id);
    
    if($this->get('security.context')->isGranted('ROLE_ADMIN') === false) {
      $where .= ' AND c.status = :status';
      $parameters['status'] = 'finalised';
    }
    
    // QUERY

    $query = $entityManager->createQuery('
      SELECT e, c, t FROM PapyrillioBeehiveBundle:Correction c
      LEFT JOIN c.tasks t JOIN c.edition e JOIN c.compilation c2 WHERE ' . $where . ' ORDER BY c.sort'
    );
    $query->setParameters($parameters);

    $corrections = $query->getResult();

    if($format === 'html'){
      return $this->render('PapyrillioBeehiveBundle:Apiary:honey.html.twig', array('corrections' => $corrections));
    } else {
      $data = array('corrections' => array());
      $data = array('count' => count($corrections));
      foreach($corrections as $correction){
        $data['corrections'][$correction->getId()] = array(
          'tm' => $correction->getTm(),
          'ddb' => $correction->getDdb(),
          'hgv' => $correction->getHgv(),
          'description' => $correction->getDescription(),
          'status' => $correction->getStatus()
        );
      }
      return new Response(json_encode(array('success' => 'true', 'data' => $data)));
    }
    
  }

}
