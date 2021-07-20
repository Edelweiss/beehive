<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

class Task
{
    public function __toString(){
      return $this->category . ': ' . $this->description;
    }
    
    public function isCleared(){
      return ($this->cleared instanceof DateTime);
    }
    
    public function markAsCleared(){
      $this->cleared = new DateTime('now');
    }
    
    public function markAsToBeDone(){
      $this->cleared = null;
    }
    
    public function getTitle(){
      return strtoupper($this->category);
    }

    private $id;
    private $category;
    private $description;
    private $cleared;
    private $correction;

    public function getId()
    {
        return $this->id;
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setCleared($cleared)
    {
        $this->cleared = $cleared;
    }

    public function getCleared()
    {
        return $this->cleared;
    }

    public function setCorrection(\App\Entity\Correction $correction)
    {
        $this->correction = $correction;
    }

    public function getCorrection()
    {
        return $this->correction;
    }
}