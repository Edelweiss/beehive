<?php

namespace App\Entity;

use App\Repository\DocketRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable;
use Doctrine\ORM\Event\LifecycleEventArgs; // prePersist
use Doctrine\ORM\Event\OnFlushEventArgs; // onFlush
use Doctrine\ORM\Event\PreUpdateEventArgs; // preUpdate
use DateTime;
use Exception;
/**
 * Papyrillio\BeehiveBundle\Entity\Docket
 */
class Docket
{
    public function __construct()
    {
        $this->editions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $text
     */
    private $text;

    /**
     * @var string $position
     */
    private $position;

    /**
     * @var string $info
     */
    private $info;

    /**
     * @var string $type
     */
    private $type = 'preamble';

    /**
     * @var integer $sort
     */
    private $sort = 0;

    /**
     * @var Papyrillio\BeehiveBundle\Entity\Edition
     */
    private $editions;

    private $compilation;

    public function setCompilation(\App\Entity\Compilation $compilation)
    {
        $this->compilation = $compilation;
    }
    public function getCompilation()
    {
        return $this->compilation;
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
     * Get Text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Get Position
     *
     * @return string 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Get Info
     *
     * @return string 
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Get Type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
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
     * Add editions
     *
     * @param Papyrillio\BeehiveBundle\Entity\Edition $editions
     */
    public function addEdition(\App\Entity\Edition $edition)
    {
        $this->editions[] = $edition;
    }

    /**
     * Get editions
     *
     */
    public function getEditions()
    {
        return $this->editions;
    }

    public function __toString(){
      return $this->getText() . ' / ' . $this->getPosition() . ': ' . $this->getInfo();
    }
    private $compilationPage;
    public function setCompilationPage($compilationPage)
    {
        $this->compilationPage = $compilationPage;
    }
    public function getCompilationPage()
    {
        return $this->compilationPage;
    }
}
