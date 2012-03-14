<?php

namespace Papyrillio\BeehiveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable;
use DateTime;
/**
 * Papyrillio\BeehiveBundle\Entity\Correction
 */
class Correction
{
    const STATUS_UNCHECKED = 'unchecked';
    const STATUS_REVIEWED  = 'reviewed';
    const STATUS_FINALISED = 'finalised';
    const STATUS_DEFAULT   = 'finalised';
    
    protected static $STATUS = array('unchecked', 'reviewed', 'finalised');

    public function __construct()
    {
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->status = self::STATUS_UNCHECKED;
        $this->creator = 'system';
        $this->created = new DateTime('now');
        
    }
    
    public function getTitle(){
      return $this->getEdition()->getSort() . ' = ' . $this->getEdition()->getTitle();
    }

    public function hasTask($category = null){
      return count($this->getTasks($category));
    }
    
    public function hasOpenTask($category = null){
      $count = 0;
      foreach($this->getTasks($category) as $task){
        if(!$task->isCleared()){
          $count++;
        }
      }
      return $count;
    }

    public function getTasks($category = null){
      if($category === null){
        return $this->tasks;
      } else {
        $tasks = new \Doctrine\Common\Collections\ArrayCollection();
        foreach($this->tasks as $task){
          if($task->getCategory() === $category){
            $tasks->add($task);
          }
        }
        return $tasks;
      }
    }
    
    public function getTaskString($category = null){
      $result = '';
      foreach($this->getTasks($category) as $task){

          $result .= ($category ? '' : $task->getCategory() . ': ') . $task->getDescription() . "; ";

      }
      return rtrim($result, '; ');
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
          return $this->ddb ? 'http://www.papyri.info/ddbdp/' . $this->ddb : null;
        case 'biblio':
          return $this->source ? 'http://www.papyri.info/biblio/' . $this->source : null;
        case 'githubddb':
          return $this->collection ? 'https://github.com/papyri/idp.data/blob/master/DDB_EpiDoc_XML/'. $this->collection . '/'. $this->collection . '.'. $this->volume . '/'. $this->collection . '.'. $this->volume . '.' . $this->document . '.xml' : null;
        case 'githubhgv':
          return $this->hgv && $this->folder ? 'https://github.com/papyri/idp.data/blob/master/HGV_meta_EpiDoc/HGV' . $this->folder . '/' . $this->hgv . '.xml' : null;
        case 'hgv':
          return $this->collection && $this->volume && $this->document ? 'http://www.papy.uni-heidelberg.de/Hauptregister/FMPro?-DB=Hauptregister_&-Format=DTableVw.htm&Publikation='. $this->collection . '&Band='. $this->volume . '&Nummer='. $this->document . '&-Max=20&-Find' : null;
        default:
          return null;
      }
    }

    /**
     * Set status
     *
     * @param text $status
     */
    public function setStatus($status)
    {
        if(in_array($status, self::$STATUS)){
          $this->status = $status;
        } else {
          $this->status = self::STATUS_DEFAULT;
        }
    }

    public function getLinks(){
      $links = array();
      foreach(array('pi' => 'papyri.info', 'githubddb' => 'github DDB', 'githubhgv' => 'github HGV', 'hgv' => 'HGV', 'biblio' => 'Biblio') as $type => $name){
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
     * @var integer $source
     */
    private $source;

    /**
     * @var string $text
     */
    private $text;

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
     * @var text $position
     */
    private $position;

    /**
     * @var text $description
     */
    private $description;

    /**
     * @var text $status
     */
    private $status;

    /**
     * @var text $creator
     */
    private $creator;

    /**
     * @var datetime $created
     */
    private $created;

    /**
     * @var Papyrillio\BeehiveBundle\Entity\Task
     */
    private $tasks;

    /**
     * @var Papyrillio\BeehiveBundle\Entity\Compilation
     */
    private $compilation;
   
    /**
     * @var Papyrillio\BeehiveBundle\Entity\Edition
     */
    private $edition;
    
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
     * Set text
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Set source
     *
     * @param integer $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * Get source
     *
     * @return integer 
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
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
     * Get status
     *
     * @return text 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set creator
     *
     * @param text $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * Get creator
     *
     * @return text 
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set created
     *
     * @param datetime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return datetime 
     */
    public function getCreated()
    {
        return $this->created;
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


    /**
     * Set edition
     *
     * @param Papyrillio\BeehiveBundle\Entity\Edition $edition
     */
    public function setEdition(\Papyrillio\BeehiveBundle\Entity\Edition $edition)
    {
        $this->edition = $edition;
    }

    /**
     * Get edition
     *
     * @return Papyrillio\BeehiveBundle\Entity\Edition 
     */
    public function getEdition()
    {
        return $this->edition;
    }
}