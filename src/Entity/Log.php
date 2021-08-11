<?php

namespace App\Entity;

use App\Repository\LogRepository;
use Doctrine\ORM\Mapping as ORM;

class Log
{
  private $id;
  private $action;
  private $loggedAt;
  private $objectId;
  private $objectClass;
  private $version;
  private $data;
  private $username;

  public function getId(){ return $this->id; }
  public function getAction(){ return $this->action; }
  public function setAction($new){ $this->action = $new; }
  public function getLoggedAt(){ return $this->loggedAt; }
  public function setLoggedAt($new){ $this->loggedAt = $new; }
  public function getObjectId(){ return $this->objectId; }
  public function setObjectId($new){ $this->objectId = $new; }
  public function getObjectClass(){ return $this->objectClass; }
  public function setObjectClass($new){ $this->objectClass = $new; }
  public function getVersion(){ return $this->version; }
  public function setVersion($new){ $this->version = $new; }
  public function getData(){ return $this->data; }
  public function setData($new){ $this->data = $new; }
  public function getUsername(){ return $this->username; }
  public function setUsername($new){ $this->username = $new; }
}