<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * Papyrillio\BeehiveBundle\Entity\Task
 */
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
    
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $category
     */
    private $category;

    /**
     * @var text $description
     */
    private $description;

    /**
     * @var datetime $cleared
     */
    private $cleared;

    /**
     * @var Papyrillio\BeehiveBundle\Entity\Correction
     */
    private $correction;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set category
     *
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * Get category
     *
     * @return string 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set cleared
     *
     * @param datetime $cleared
     */
    public function setCleared($cleared)
    {
        $this->cleared = $cleared;
    }

    /**
     * Get cleared
     *
     * @return datetime 
     */
    public function getCleared()
    {
        return $this->cleared;
    }

    /**
     * Set correction
     *
     * @param Papyrillio\BeehiveBundle\Entity\Correction $correction
     */
    public function setCorrection(\Papyrillio\BeehiveBundle\Entity\Correction $correction)
    {
        $this->correction = $correction;
    }

    /**
     * Get correction
     *
     * @return Papyrillio\BeehiveBundle\Entity\Correction 
     */
    public function getCorrection()
    {
        return $this->correction;
    }
}