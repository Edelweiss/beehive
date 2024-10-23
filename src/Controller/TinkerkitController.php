<?php

namespace App\Controller;

use App\Entity\Compilation;
use App\Entity\Correction;
use App\Entity\IndexEntry;
use Symfony\Component\HttpFoundation\Response;

class TinkerkitController extends BeehiveController{
  public function manageIndexEntryAssignments($compilationId, $type, $topic, $search = null): Response {
    return $this->render('tinkerkit/manageIndexEntryAssignments.html.twig', [
      'compilationId'   => $compilationId, 'type' => $type, 'topic' => $topic, 'search' => $search,
      'compilationList' => $this->getCompilationList(),
      'topicList'       => $this->getTopicList(),
      'indexEntryList'  => $this->getIndexEntryList($compilationId, $type, $topic),
      'correctionList'  => $this->getCorrectionList($compilationId, $search)]);
  }
  private function getCompilationList(){
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Compilation::class);
    return $repository->findBy(['collection' => 'BL'], ['volume' => 'ASC', 'id' => 'ASC']);
  }
  private function getTopicList(){
    $res = $this->getDoctrine()->getManager()->createQueryBuilder()
      ->select('ie.topic')->distinct()
      ->from('App\Entity\IndexEntry', 'ie')
      ->orderBy('ie.sort', 'ASC')
      ->getQuery()->getResult();

    $topicList = [];
    foreach($res as $topicItem){
      $topicList[] = $topicItem['topic'];
    }
    return $topicList;
  }
  private function getIndexEntryList($compilationId, $type, $topic){
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(IndexEntry::class);

    $queryB = $entityManager->createQueryBuilder()
     ->select('ie')
     ->from('App\Entity\IndexEntry', 'ie')
     ->join('App\Entity\Compilation', 'c')
     ->where('c.id = :compilationId AND ie.topic = :topic AND ie.type = :type')
     ->orderBy('ie.sort', 'ASC')
     ->setParameters(['type'=> $type, 'topic' => $topic, 'compilationId' => $compilationId]);
    return $queryB->getQuery()->getResult();
  }
  private function getCorrectionList($compilationId, $search){
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Correction::class);

    $queryB = $entityManager->createQueryBuilder()
     ->select(['c', 'e'])
     ->from('App\Entity\Correction', 'c')
     ->join('c.edition', 'e')
     ->where('c.compilation = :compilationId' . ($search ? ' AND c.description LIKE :search' : ''))
     ->orderBy('c.sort', 'ASC')
     ->setParameters($search ? ['compilationId' => $compilationId, 'search' => '%' . $search . '%'] : ['compilationId' => $compilationId]);
    return $queryB->getQuery()->getResult();
  }

/*
  public function assignIndexEntry($correctionId, $indexEntryIdList): Response {
    $correction = $this->getDoctrine()->getManager()->getRepository(Correction::class)->findOneBy(array('id' => $correctionId));
    foreach($indexEntryIdList as $indexEntryId){
      $indexEntry = $this->getDoctrine()->getManager()->getRepository(IndexEntry::class)->findOneBy(array('id' => $indexEntryId));
      $correction->addIndexEntry($indexEntry);
    }
    $this->getDoctrine()->getManager()->persist($correction);
    $this->getDoctrine()->getManager()->flush();

    return $this->redirect($this->generateUrl('PapyrillioBeehive_IndexEntryManageAssignments', array('correctionId' => $correctionId))); // CL todo
  }

  public function revokeAssignment($correctionId, $indexEntryId): Response {
    $correction = $this->getDoctrine()->getManager()->getRepository(Correction::class)->findOneBy(array('id' => $correctionId));
    $indexEntry = $this->getDoctrine()->getManager()->getRepository(IndexEntry::class)->findOneBy(array('id' => $indexEntryId));

    $correction->getRegisterEntries()->removeElement($register);
    $indexEntry->getCorrections()->removeElement($correction);

    $this->getDoctrine()->getManager()->persist($correction);
    $this->getDoctrine()->getManager()->flush();

    return $this->redirect($this->generateUrl('PapyrillioBeehive_IndexEntryManageAssignments', array('correctionId' => $correctionId))); // CL todo
  }
    */
}
