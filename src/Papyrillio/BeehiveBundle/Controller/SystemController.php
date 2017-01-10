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
    $query->setFirstResult(0)->setMaxResults(1000);
    
    $batchSize = 100;
    $batchCount = 0;
    $logs = array();
    
    foreach($query->iterate() AS $row) {
      $correction = $row[0];
      $oldSort = $correction->getSort();
      $correction->setSortValues();
      if($oldSort !== $correction->getSort()){
        $logs[] = array('id' => $correction->getId(), 'message' => $oldSort . ' → ' . $correction->getSort());
      }
      if(($batchCount++ % $batchSize) == 0) {
        $entityManager->flush(); // Executes all updates.
        $entityManager->clear(); // Detaches all objects from Doctrine!
      }
    }

    $entityManager->flush(); // Executes all updates.
    $entityManager->clear(); // Detaches all objects from Doctrine!
    return $this->render('PapyrillioBeehiveBundle:System:sort.html.twig', array('batchCount' => $batchCount, 'logs' => $logs));
  }

  public function checkSortAction($id){
    $entityManager = $this->getDoctrine()->getEntityManager();
    $repository = $entityManager->getRepository('PapyrillioBeehiveBundle:Correction');
    $correction = $repository->findOneBy(array('id' => $id));
    $oldSort = '';
    if($correction){
			$oldSort = $correction->getSort();
			$correction->setSortValues();
    }
    
    return $this->render('PapyrillioBeehiveBundle:System:checkSort.html.twig', array('correction' => $correction, 'oldSort' => $oldSort));
  }
}
