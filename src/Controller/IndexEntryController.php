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
      SELECT i, c, e, c FROM App\Entity\IndexEntry i
      LEFT JOIN i.correction c LEFT JOIN c.edition e JOIN c.compilation c2 WHERE i.type = :type ORDER BY i.topic, i.phrase'
    );

    $parameters = array('type' => $type);
    
    $query->setParameters($parameters);

    $indexEntries = $query->getResult();
    return $this->render('indexEntry/list.html.twig', ['indexEntries' => $indexEntries]);
  }

}
