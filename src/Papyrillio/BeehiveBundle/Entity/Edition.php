<?php

namespace Papyrillio\BeehiveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Papyrillio\BeehiveBundle\Entity\Edition
 */
class Edition
{
    const MATERIAL_PAPYRUS = 'Papyrus';
    const MATERIAL_OSTRACON = 'Ostrakon';
    const MATERIAL_DEFAULT = 'Papyrus';
    
    protected static $MATERIAL = array('Papyrus', 'Ostrakon');

    /**
     * Set material
     *
     * @param text $material
     */
    public function setMaterial($material)
    {
        if(in_array($material, self::$MATERIAL)){
          $this->material = $material;
        } else {
          $this->material = self::MATERIAL_DEFAULT;
        }
    }

    public function __construct()
    {
        $this->corrections = new \Doctrine\Common\Collections\ArrayCollection();
        $this->material = self::MATERIAL_DEFAULT;
    }

    /**
     * Set title
     *
     * @param text $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        $matches = array();
        if(preg_match('/\((.+)\)/', $title, $matches)){
          $this->remark = $matches[1];
        }
        if(preg_match('/ (\d+[ABCDEFGHIJKLMNOPQRSTUVWXYZ]?|\d+ \d+|\d+\. \d+|\d+ Gr\. \d+)($| )/', $title, $matches)){
          $this->volume = $matches[1];
        }
        if(preg_match('/^([^\(\d]+)/', $title, $matches)){
          $this->collection = rtrim($matches[1], ' ');
        }
    }
    
    public function __toString(){
      return implode(';', array($this->id, $this->sort, $this->title, $this->collection, $this->volume, $this->remark, $this->material));
    }

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $key
     */
    private $sort;

    /**
     * @var text $title
     */
    private $title;
    /**
     * @var text $collection
     */
    private $collection;

    /**
     * @var integer $volume
     */
    private $volume;

    /**
     * @var text $remark
     */
    private $remark;

    /**
     * @var text $material
     */
    private $material;

    /**
     * @var Papyrillio\BeehiveBundle\Entity\Correction
     */
    private $corrections;

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
     * Set sort
     *
     * @param integer $sort
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * Get sort
     *
     * @return integer 
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Get title
     *
     * @return text 
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * Set collection
     *
     * @param text $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * Get collection
     *
     * @return text 
     */
    public function getCollection()
    {
        return $this->collection;
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
     * Set remark
     *
     * @param text $remark
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;
    }

    /**
     * Get remark
     *
     * @return text 
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * Get material
     *
     * @return text 
     */
    public function getMaterial()
    {
        return $this->material;
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