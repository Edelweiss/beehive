<?php
namespace App\Entity;

use App\Repository\IndexEntryRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Compilation;

class IndexEntry
{
    private $id;
    private $type;
    private $topic;
    private $tab;
    private $papy_new;
    private $greek_new;
    private $lemma;
    private $sort;
    private $phrase;
    private $compilations;
    private $corrections;

    public function __construct()
    {
        $this->compilations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->corrections = new \Doctrine\Common\Collections\ArrayCollection();
    }

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

    public function setTab($topic)
    {
        $this->tab = $tab;
    }

    public function getTab()
    {
        return $this->tab;
    }

    public function setPapyNew($papyNew)
    {
        $this->papy_new = $papyNew;
    }

    public function getPapyNew()
    {
        return $this->papy_new;
    }

    public function setLemma($lemma)
    {
        $this->lemma = $lemma;
    }

    public function getLemma()
    {
        return $this->lemma;
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setPhrase($phrase)
    {
        $this->phrase = $phrase;
    }

    public function getPhrase()
    {
        return $this->phrase;
    }

    public function addCorrection(\App\Entity\Correction $correction)
    {
        $this->corrections[] = $correction;
    }

    public function getCorrections()
    {
        return $this->corrections;
    }

    public function addCcompilation(\App\Entity\Compilation $compilation)
    {
        $this->compilations[] = $compilation;
    }

    public function getCompilations()
    {
        return $this->compilations;
    }
}