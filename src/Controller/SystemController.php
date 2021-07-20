<?php

namespace App\Controller;

use App\Entity\Correction;
use App\Entity\Compilation;
use App\Entity\Edition;
use App\Entity\Task;
use App\Entity\IndexEntry;
use Symfony\Component\HttpFoundation\Response;
use DateTime;

class SystemController extends BeehiveController{
  public function sort($editionId): Response {
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Correction::class);
    $query = 'SELECT e, c FROM App\Entity\Correction c JOIN c.edition e';
    if(is_numeric($editionId)){
      $query .= ' WHERE e.id = ' . $editionId;
    }
    $query = $entityManager->createQuery($query);
    // $query->setFirstResult(0)->setMaxResults(1000);

    $batchSize = 100;
    $batchCount = 0;
    $logs = array();

    foreach($query->iterate() AS $row) {
      $correction = $row[0];
      $oldSort = $correction->getSort();
      $correction->setSortValues();
      //if($oldSort !== $correction->getSort()){
        $logs[] = array('id' => $correction->getId(), 'message' => $oldSort . ' → ' . $correction->getSort() . ' (sort key length: ' . mb_strlen($correction->getSort()) . ')');
      //}
      if(($batchCount++ % $batchSize) == 0) {
        $entityManager->flush(); // Executes all updates.
        $entityManager->clear(); // Detaches all objects from Doctrine!
      }
    }

    $entityManager->flush(); // Executes all updates.
    $entityManager->clear(); // Detaches all objects from Doctrine!
    return $this->render('system/sort.html.twig', ['batchCount' => $batchCount, 'logs' => $logs]);
  }

  public function checkSort($id): Response {
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Correction::class);
    $correction = $repository->findOneBy(array('id' => $id));
    $oldSort = '';
    if($correction){
			$oldSort = $correction->getSort();
			$correction->setSortValues();
    }
    
    return $this->render('system/checkSort.html.twig', ['correction' => $correction, 'oldSort' => $oldSort]);
  }
}
