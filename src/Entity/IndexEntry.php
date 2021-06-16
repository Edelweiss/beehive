<?php
namespace App\Entity;

use App\Repository\IndexEntryRepository;
use Doctrine\ORM\Mapping as ORM;

class IndexEntry
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var string $topic
     */
    private $topic;

    /**
     * @var text $phrase
     */
    private $phrase;

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
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set topic
     *
     * @param string $topic
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;
    }

    /**
     * Get topic
     *
     * @return string 
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Set phrase
     *
     * @param text $phrase
     */
    public function setPhrase($phrase)
    {
        $this->phrase = $phrase;
    }

    /**
     * Get phrase
     *
     * @return text 
     */
    public function getPhrase()
    {
        return $this->phrase;
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