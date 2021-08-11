<?php

namespace App\Repository;

use App\Entity\Log;
use App\Entity\Correction;
use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LogRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Log::class);
  }

  public function getLogs(\App\Entity\Correction $correction){
    $dql = 'SELECT l FROM App\Entity\Log l WHERE l.objectClass = ?1 AND l.objectId = ?2 ORDER BY l.version DESC';

    return $this->getEntityManager()->createQuery($dql)
      ->setParameter(1, 'App\Entity\Correction')
      ->setParameter(2, $correction->getId())
      ->getResult();
  }

  public function getTaskLogs(\App\Entity\Correction $correction){
    $logs = [];
    $dql = 'SELECT l FROM App\Entity\Log l WHERE l.objectClass = ?1 AND l.objectId = ?2 ORDER BY l.version DESC';
    foreach ($correction->getTasks() as $task) {
      $logs = array_merge($logs, $this->getEntityManager()->createQuery($dql)
        ->setParameter(1, 'App\Entity\Task')
        ->setParameter(2, $task->getId())
        ->getResult());
    }
    return $logs;
  }
}