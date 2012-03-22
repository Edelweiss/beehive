<?php

namespace Papyrillio\BeehiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Papyrillio\BeehiveBundle\Entity\Correction;
use Papyrillio\BeehiveBundle\Entity\Compilation;
use Papyrillio\BeehiveBundle\Entity\Edition;
use Papyrillio\BeehiveBundle\Entity\Task;
use Papyrillio\BeehiveBundle\Entity\IndexEntry;
use DateTime;

class SystemController extends BeehiveController{
  public function sortAction(){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');
    $query = $entityManager->createQuery('SELECT c FROM PapyrillioBeehiveBundle:Correction c');
    
    $batchSize = 20;
    $batchCount = 0;
    
    foreach($query->iterate() AS $row) {
      $correction = $row[0];
      $correction->setSortValues();
      if(($batchCount++ % $batchSize) == 0) {
        $entityManager->flush(); // Executes all updates.
        $entityManager->clear(); // Detaches all objects from Doctrine!
      }
    }

    $entityManager->flush(); // Executes all updates.
    $entityManager->clear(); // Detaches all objects from Doctrine!
    return $this->render('PapyrillioBeehiveBundle:System:sort.html.twig', array('batchCount' => $batchCount));
  }
}
