<?php

namespace Papyrillio\BeehiveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable;
use Doctrine\ORM\Event\LifecycleEventArgs; // prePersist
use Doctrine\ORM\Event\OnFlushEventArgs; // onFlush
use Doctrine\ORM\Event\PreUpdateEventArgs; // preUpdate
use DateTime;
use Exception;
/**
 * Papyrillio\BeehiveBundle\Entity\Register
 */
class Register
{
    public function __construct()
    {
        $this->corrections = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set ddb
     *
     * @param string $ddb
     */
    public function setDdb($ddb)
    {
        $this->ddb = $ddb;

        $tokenList = explode(';', $this->ddb);
        $this->collection = array_key_exists(0, $tokenList) ? $tokenList[0] : '';
        $this->volume     = array_key_exists(1, $tokenList) ? $tokenList[1] : '';
        $this->document   = array_key_exists(2, $tokenList) ? $tokenList[2] : '';
    }

    /**
     * Set tm
     *
     * @param integer $tm
     */
    public function setTm($tm)
    {
        $this->tm = $tm;
        $this->folder = ceil($this->tm / 1000.0);
    }

    /**
     * Set hgv
     *
     * @param string $hgv
     */
    public function setHgv($hgv)
    {
        $this->hgv = $hgv;
        $this->setTm(preg_replace('/[^\d]+/', '', $hgv) * 1);
    }

    public function getLink($type = 'pi'){
      switch($type){
        case 'pi':
          return $this->ddb ? 'http://www.papyri.info/ddbdp/' . $this->ddb : ($this->hgv ? 'http://www.papyri.info/hgv/' . $this->hgv : null);
        case 'githubddb':
          return $this->collection ? 'https://github.com/papyri/idp.data/blob/master/DDB_EpiDoc_XML/'. $this->collection . '/'. $this->collection . '.'. $this->volume . '/'. $this->collection . '.'. $this->volume . '.' . $this->document . '.xml' : null;
        case 'githubhgv':
          return $this->hgv && $this->folder ? 'https://github.com/papyri/idp.data/blob/master/HGV_meta_EpiDoc/HGV' . $this->calcFolder($this->hgv) . '/' . $this->hgv . '.xml' : null;
        case 'hgv':
          return $this->hgv ? 'https://aquila.zaw.uni-heidelberg.de/hgv/' . $this->hgv : null;
        case 'tm':
          return $this->tm ? 'https://www.trismegistos.org/dataservices/texrelations/xml/' . $this->tm : null;
        default:
          return null;
      }
    }
    
    public function calcFolder($tmOrHgv){
      $id = preg_replace('/[a-z]+/', '', $tmOrHgv);
      return ceil($id / 1000) . '';
    }
    
    public function getLinks(){
      $links = array();
      foreach(array('pi' => 'papyri.info', 'githubddb' => 'github DDB', 'githubhgv' => 'github HGV', 'hgv' => 'HGV', 'tm' => 'TM') as $type => $name){
        if($this->getLink($type)){
          $links[$name] = $this->getLink($type);
        }
      }
      return $links;
    }

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $tm
     */
    private $tm;

    /**
     * @var integer $folder
     */
    private $folder;

    /**
     * @var string $hgv
     */
    private $hgv;

    /**
     * @var string $ddb
     */
    private $ddb;
    /**
     * @var string $collection
     */
    private $collection;

    /**
     * @var string $volume
     */
    private $volume;

    /**
     * @var string $document
     */
    private $document;

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
     * Get tm
     *
     * @return integer 
     */
    public function getTm()
    {
        return $this->tm;
    }

    /**
     * Set folder
     *
     * @param integer $folder
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
    }

    /**
     * Get folder
     *
     * @return integer 
     */
    public function getFolder()
    {
        return $this->folder;
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
     * Get ddb
     *
     * @return string 
     */
    public function getDdb()
    {
        return $this->ddb;
    }

    /**
     * Set collection
     *
     * @param string $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * Get collection
     *
     * @return string 
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set volume
     *
     * @param string $volume
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;
    }

    /**
     * Get volume
     *
     * @return string 
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set document
     *
     * @param string $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
    }

    /**
     * Get document
     *
     * @return string 
     */
    public function getDocument()
    {
        return $this->document;
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
     */
    public function getCorrections()
    {
        return $this->corrections;
    }
}