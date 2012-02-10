<?php

namespace Papyrillio\BeehiveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Papyrillio\BeehiveBundle\Entity\Correction
 */
class Correction
{


    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $bl
     */
    private $bl;

    /**
     * @var integer $tm
     */
    private $tm;

    /**
     * @var string $hgv
     */
    private $hgv;

    /**
     * @var string $ddb
     */
    private $ddb;

    /**
     * @var text $position
     */
    private $position;

    /**
     * @var text $description
     */
    private $description;

    /**
     * @var Papyrillio\BeehiveBundle\Entity\Task
     */
    private $tasks;

    /**
     * @var Papyrillio\BeehiveBundle\Entity\Compilation
     */
    private $compilation;

    public function __construct()
    {
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * Set bl
     *
     * @param integer $bl
     */
    public function setBl($bl)
    {
        $this->bl = $bl;
    }

    /**
     * Get bl
     *
     * @return integer 
     */
    public function getBl()
    {
        return $this->bl;
    }

    /**
     * Set tm
     *
     * @param integer $tm
     */
    public function setTm($tm)
    {
        $this->tm = $tm;
    }

    /**
     * Get tm
     *
     * @return integer 
     */
    public function getTm()
    {
        return $this->tm;
    }

    /**
     * Set hgv
     *
     * @param string $hgv
     */
    public function setHgv($hgv)
    {
        $this->hgv = $hgv;
    }

    /**
     * Get hgv
     *
     * @return string 
     */
    public function getHgv()
    {
        return $this->hgv;
    }

    /**
     * Set ddb
     *
     * @param string $ddb
     */
    public function setDdb($ddb)
    {
        $this->ddb = $ddb;
    }

    /**
     * Get ddb
     *
     * @return string 
     */
    public function getDdb()
    {
        return $this->ddb;
    }

    /**
     * Set position
     *
     * @param text $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get position
     *
     * @return text 
     */
    public function getPosition()
    {
        return $this->position;
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
     * Add tasks
     *
     * @param Papyrillio\BeehiveBundle\Entity\Task $tasks
     */
    public function addTask(\Papyrillio\BeehiveBundle\Entity\Task $tasks)
    {
        $this->tasks[] = $tasks;
    }

    /**
     * Get tasks
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Set compilation
     *
     * @param Papyrillio\BeehiveBundle\Entity\Compilation $compilation
     */
    public function setCompilation(\Papyrillio\BeehiveBundle\Entity\Compilation $compilation)
    {
        $this->compilation = $compilation;
    }

    /**
     * Get compilation
     *
     * @return Papyrillio\BeehiveBundle\Entity\Compilation 
     */
    public function getCompilation()
    {
        return $this->compilation;
    }
}