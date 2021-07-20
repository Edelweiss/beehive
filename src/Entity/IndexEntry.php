<?php
namespace App\Entity;

use App\Repository\IndexEntryRepository;
use Doctrine\ORM\Mapping as ORM;

class IndexEntry
{
    private $id;
    private $type;
    private $phrase;
    private $topic;
    private $correction;

    public function getId()
    {
        return $this->id;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setTopic($topic)
    {
        $this->topic = $topic;
    }

    public function getTopic()
    {
        return $this->topic;
    }

    public function setPhrase($phrase)
    {
        $this->phrase = $phrase;
    }

    public function getPhrase()
    {
        return $this->phrase;
    }

    public function setCorrection(\App\Entity\Correction $correction)
    {
        $this->correction = $correction;
    }

    public function getCorrection()
    {
        return $this->correction;
    }
}