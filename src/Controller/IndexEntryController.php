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

  public function list($type = 'Neues Wort', $compilationId = null): Response{
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(IndexEntry::class);
    $indexEntries = null;
    $compilation = null;
    $compilations = [];
    $unassigned = [];

    if($compilationId){
      $query = $entityManager->createQuery('
        SELECT i, c, comp FROM App\Entity\IndexEntry i
        JOIN i.corrections c LEFT JOIN i.compilations comp WHERE i.type = :type AND c.compilation = :compilationId ORDER BY i.sort');
      $parameters = ['type' => $type, 'compilationId' => $compilationId];
      $query->setParameters($parameters);
      $indexEntries = $query->getResult();

      $query = $entityManager->createQuery('
        SELECT i, c FROM App\Entity\IndexEntry i
        INNER JOIN i.compilations comp LEFT JOIN i.corrections c WHERE i.type = :type and comp = :compilationId and c.id IS NULL ORDER BY i.sort');
      $parameters = ['type' => $type, 'compilationId' => $compilationId];
      $query->setParameters($parameters);
      $query->setMaxResults(1000);
      $unassigned = $query->getResult();

      $repositoryCompilation = $entityManager->getRepository(Compilation::class);
      $compilation = $repositoryCompilation->findOneBy(['id' => $compilationId]);
      $compilations = $repositoryCompilation->findBy(['collection' => 'BL']);
    } else {
      $query = $entityManager->createQuery('
        SELECT i, c, comp FROM App\Entity\IndexEntry i
        JOIN i.compilations c LEFT JOIN i.compilations comp WHERE i.type = :type ORDER BY i.sort');
      $parameters = ['type' => $type];
      $query->setParameters($parameters);
      $indexEntries = $query->getResult();
    }

    $topic_indexEntries = [];
    foreach($indexEntries as $ie){
      $topic_indexEntries[$ie->getTopic()][$ie->getId()] = $ie;
    }

    return $this->render('indexEntry/list.html.twig', ['type' => $type, 'compilation' => $compilation, 'compilations' => $compilations, 'indexEntries' => $topic_indexEntries, 'unassigned' => $unassigned]);
  }

}
