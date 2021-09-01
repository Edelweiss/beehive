<?php

namespace App\Controller;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class LogController extends BeehiveController{

  public function showDeletedCorrections(): Response {
    $entityManager = $this->getDoctrine()->getManager();
    $repository = $entityManager->getRepository(Log::class);
    $limit = 500;

    $query = $entityManager->createQuery('SELECT COUNT(l) from  App\Entity\Log l WHERE l.action = :action and l.objectClass = :objectClass')->setParameters(['action' => 'remove', 'objectClass' => 'App\Entity\Correction']);
    $count = $query->getSingleScalarResult();

    //$query = $entityManager->createQuery('SELECT l FROM App\Entity\Log l JOIN App\Entity\Log r WITH l.objectId = r.objectId AND l.objectClass = r.objectClass AND l.version = r.version - 1 WHERE r.action = :action AND l.objectClass LIKE :objectClass ORDER BY r.loggedAt DESC ')->setMaxResults($limit);
    $query = $entityManager->createQuery('SELECT l.id, l.objectId, l.objectClass, l.data, r.username, r.action, r.version, r.loggedAt FROM App\Entity\Log l JOIN App\Entity\Log r WITH l.objectId = r.objectId AND l.objectClass = r.objectClass AND l.version = r.version - 1 WHERE r.action = :action AND l.objectClass LIKE :objectClass ORDER BY r.loggedAt DESC ')->setMaxResults($limit);

    //SELECT * FROM `log` u JOIN log r ON u.object_id = r.object_id AND u.object_class = r.object_class AND u.object_class LIKE '%Correction' AND r.action = 'remove' AND u.version = r.version - 1
    $parameters = [];
    $parameters['action'] = 'remove';
    $parameters['objectClass'] = '%Correction%';
    $query->setParameters($parameters);
    $logs = $query->getResult();
    
    return $this->render('log/log.html.twig', ['logs' => $logs, 'count' => $count, 'limit' => $limit]);
    
  }
}
