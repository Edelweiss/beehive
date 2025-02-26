<?php
namespace App\Entity;

use App\Repository\VolumeRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Register;

class Volume
{
    private $id;
    private $hybrid;
    private $title;
    private $sort;
    private $registerEntries;

    public function setId($id){
      $this->id = $id;
    }
    public function getId()
    {
        return $this->id;
    }
    
    public function setHybrid($hybrid){
      $this->hybrid = $hybrid;
    }
    public function getHybrid()
    {
        return $this->hybrid;
    }
    
    public function setTitle($title){
      $this->title = $title;
    }
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setSort($sort){
      $this->sort = $sort;
    }
    public function getSort()
    {
        return $this->sort;
    }

    public function __construct()
    {
        $this->registerEntries = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function addRegisterEntry(\App\Entity\Register $registerEntry)
    {
        $this->registerEntries[] = $registerEntry;
    }

    public function getRegisterEntries()
    {
        return $this->registerEntries;
    }

}