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
        $this->ddbCollection = array_key_exists(0, $tokenList) ? $tokenList[0] : '';
        $this->ddbVolume     = array_key_exists(1, $tokenList) ? $tokenList[1] : '';
        $this->ddbDocument   = array_key_exists(2, $tokenList) ? $tokenList[2] : '';
    }

    /**
     * Set dclp
     *
     * @param string $dclp
     */
    public function setDclp($dclp)
    {
        $this->dclp = $dclp;

        $tokenList = explode(';', $this->dclp);
        $this->dclpCollection = array_key_exists(0, $tokenList) ? $tokenList[0] : '';
        $this->dclpVolume     = array_key_exists(1, $tokenList) ? $tokenList[1] : '';
        $this->dclpDocument   = array_key_exists(2, $tokenList) ? $tokenList[2] : '';
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
          if($this->ddb){
            return 'https://www.papyri.info/ddbdp/' . $this->ddb;
          } elseif($this->dclp){
            return 'https://www.papyri.info/dclp/' . $this->tm;
          } elseif($this->hgv) {
            return 'https://www.papyri.info/hgv/' . $this->hgv;
          } else {
            return null;
          }
        case 'githubddb':
          if($this->ddb){
            return 'https://github.com/papyri/idp.data/blob/master/DDB_EpiDoc_XML/' . $this->ddbCollection . '/' . $this->ddbCollection . '.' . $this->ddbVolume . '/' . $this->ddbCollection . '.' . $this->ddbVolume . '.' . $this->ddbDocument . '.xml';
          } elseif($this->dclp && $this->tm){
            return 'https://github.com/papyri/idp.data/blob/master/DCLP/' . $this->calcFolder($this->tm) . '/' . $this->tm . '.xml';
          } else {
            return null;
          }
        case 'githubhgv':
          return $this->hgv ? 'https://github.com/papyri/idp.data/blob/master/HGV_meta_EpiDoc/HGV' . $this->calcFolder($this->hgv) . '/' . $this->hgv . '.xml' : null;
        case 'hgv':
          return $this->hgv ? 'https://aquila.zaw.uni-heidelberg.de/hgv/' . $this->hgv : null;
        case 'tm':
          return $this->tm ? 'https://www.trismegistos.org/text/' . $this->tm : null;
        case 'tmxml':
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
      foreach(array('pi' => 'papyri.info', 'githubddb' => 'github DDB', 'githubhgv' => 'github HGV', 'hgv' => 'HGV', 'tm' => 'TM', 'tmxml' => 'TM (XML)') as $type => $name){
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
     * @var string $hgv
     */
    private $hgv;

    /**
     * @var string $ddb
     */
    private $ddb;
    /**
     * @var string $ddbCollection
     */
    private $ddbCollection;

    /**
     * @var string $ddbVolume
     */
    private $ddbVolume;

    /**
     * @var string $ddbDocument
     */
    private $ddbDocument;

    /**
     * @var string $dclp
     */
    private $dclp;
    /**
     * @var string $dclpCollection
     */
    private $dclpCollection;

    /**
     * @var string $dclpVolume
     */
    private $dclpVolume;

    /**
     * @var string $dclpDocument
     */
    private $dclpDocument;

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
     * Get hgv
     *
     * @return string 
     */
    public function getHgv()
    {
        return $this->hgv;
    }

    /**
     * Get DDB
     *
     * @return string 
     */
    public function getDdb()
    {
        return $this->ddb;
    }

    /**
     * Get DDB collection
     *
     * @return string 
     */
    public function getDdbCollection()
    {
        return $this->ddbCollection;
    }

    /**
     * Get DDB volume
     *
     * @return string 
     */
    public function getDdbVolume()
    {
        return $this->ddbVolume;
    }

    /**
     * Get DDB document
     *
     * @return string 
     */
    public function getDdbDocument()
    {
        return $this->ddbDocument;
    }

    /**
     * Get DCLP
     *
     * @return string 
     */
    public function getDclp()
    {
        return $this->dclp;
    }

    /**
     * Get DCLP collection
     *
     * @return string 
     */
    public function getDclpCollection()
    {
        return $this->dclpCollection;
    }


    /**
     * Get DCLP volume
     *
     * @return string 
     */
    public function getDclpVolume()
    {
        return $this->dclpVolume;
    }

    /**
     * Get DCLP document
     *
     * @return string 
     */
    public function getDclpDocument()
    {
        return $this->dclpDocument;
    }

    /**
     * Add corrections
     *
     * @param Papyrillio\BeehiveBundle\Entity\Correction $corrections
     */
    public function addCorrection(\Papyrillio\BeehiveBundle\Entity\Correction $correction)
    {
        $this->corrections[] = $correction;
    }

    /**
     * Get corrections
     *
     */
    public function getCorrections()
    {
        return $this->corrections;
    }
    
    public function __toString(){
      $caption = ($this->ddb ? $this->ddb . ' ' : '');
      $caption .= ($this->hgv || $this->tm ? 'TM/HGV ' : '');
      $caption .= ($this->tm ? $this->tm . ($this->hgv && ($this->hgv != $this->tm) ? ' (' . str_replace($this->tm, '', $this->hgv) . ')' : '') : $this->hgv);
      return $caption;
    }
}