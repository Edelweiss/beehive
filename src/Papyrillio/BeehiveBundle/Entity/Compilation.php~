<?php
namespace Papyrillio\BeehiveBundle\Entity;

class Compilation
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $volume
     */
    private $volume;

    /**
     * @var date $start
     */
    private $start;

    /**
     * @var date $end
     */
    private $end;

    /**
     * @var datetime $publication
     */
    private $publication;

    /**
     * @var Papyrillio\BeehiveBundle\Entity\Correction
     */
    private $corrections;

    public function __construct()
    {
        $this->corrections = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set volume
     *
     * @param integer $volume
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;
    }

    /**
     * Get volume
     *
     * @return integer 
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set start
     *
     * @param date $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * Get start
     *
     * @return date 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param date $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * Get end
     *
     * @return date 
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set publication
     *
     * @param datetime $publication
     */
    public function setPublication($publication)
    {
        $this->publication = $publication;
    }

    /**
     * Get publication
     *
     * @return datetime 
     */
    public function getPublication()
    {
        return $this->publication;
    }

    /**
     * Add corrections
     *
     * @param Papyrillio\BeehiveBundle\Entity\Correction $corrections
     */
    public function addCorrection(\Papyrillio\BeehiveBundle\Entity\Correction $corrections)
    {
        $this->corrections[] = $corrections;
    }

    /**
     * Get corrections
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCorrections()
    {
        return $this->corrections;
    }
}