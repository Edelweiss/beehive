<?php
namespace App\EventListener;

use App\Entity\Correction;
use App\Entity\Task;
use App\Entity\Log;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class Loggable
{
    // the entity listener methods receive two arguments:
    // the entity instance and the lifecycle event
    #public function postLoad(User $user, LifecycleEventArgs $event): void
    #{
        // ... do something to notify the changes
    #}

    public function postCorrectionPersist(Correction $correction, LifecycleEventArgs $event): void
    {
        $this->logCorrection('create', $correction, $event);
    }

    public function postCorrectionUpdate(Correction $correction, LifecycleEventArgs $event): void
    {
        $this->logCorrection('update', $correction, $event);
    }

    public function postTaskPersist(Task $task, LifecycleEventArgs $event): void
    {
        $this->logTask('create', $task, $event);
    }

    public function postTaskUpdate(Task $task, LifecycleEventArgs $event): void
    {
        $this->logTask('update', $task, $event);
    }

    private function logCorrection($action, Correction $correction, LifecycleEventArgs $event): void{
        $log = $this->getLog('App\Entity\Correction', $correction->getId(), $event->getEntityManager());

        $log->setAction($action);

        $log->setData([
            'position' => $correction->getPosition(),
            'description' => $correction->getDescription(),
            'status' => $correction->getStatus()
        ]);

        $this->persistLog($log, $event->getEntityManager());
    }

    private function logTask($action, Task $task, LifecycleEventArgs $event): void{
        $log = $this->getLog(Task::class, $task->getId(), $event->getEntityManager());

        $log->setAction($action);

        $log->setData([
            'description' => $task->getDescription(),
            'cleared' => $task->getCleared()
        ]);

        $this->persistLog($log, $event->getEntityManager());
        
    }

    private function getLog($objectClass, $objectId, $entityManager){
        $log = new Log();
        $log->setUsername('xxx'); # retrieve somehow
        $log->setObjectClass($objectClass);
        $log->setObjectId($objectId);

        #$logRepository = $entityManager->getRepository(Log::class);
        $query = $entityManager->createQuery('SELECT MAX(l.version) FROM App\Entity\Log l WHERE l.objectClass = ?0 AND l.objectId = ?1 GROUP BY l.objectId');
        $query->setParameters([$objectClass, $objectId]);
        try{
            if($v = $query->getOneOrNullResult()){
                #dd(array_pop($v));
                $log->setVersion(array_pop($v) + 1);
            }
        } catch (Exception $e) {
        }
        return $log;
    }
    private function persistLog(Log $log, $entityManager){
        $entityManager->persist($log);
        $entityManager->flush();
    }
}