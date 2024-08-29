<?php

namespace App\Controller;

use App\Entity\Correction;
use App\Entity\Compilation;
use App\Entity\IndexEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use DateTime;

class IndexEntryController extends BeehiveController{

  public function list($type = 'Neues Wort'): Response{
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(IndexEntry::class);

    $query = $entityManager->createQuery('
      SELECT i, c FROM App\Entity\IndexEntry i
      LEFT JOIN i.compilations c WHERE i.type = :type ORDER BY i.sort'
    );

    $parameters = array('type' => $type);
    
    $query->setParameters($parameters);

    $indexEntries = $query->getResult();
    $topic_indexEntries = [];
    foreach($indexEntries as $ie){
      $topic_indexEntries[$ie->getTopic()][$ie->getId()] = $ie;
    }


    return $this->render('indexEntry/list.html.twig', ['type' => $type, 'indexEntries' => $topic_indexEntries]);
  }

}
