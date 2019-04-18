<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InstagramHashesRepository")
 */
class InstagramHashes
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @ORM\Column(type="text")
     */
    private $hashName;

    /**
     * @ORM\Column(type="integer")
     */
    private $hashId;
    /**
     * @ORM\Column(type="integer")
     */
    private $lastCheck;
    /**
     * @ORM\Column(type="date")
     */
    private $numberOfChecks;
    /**
     * @ORM\Column(type="boolean")
     */
    private $permanentlyBlocked;
    /**
     * @ORM\Column(type="boolean")
     */
    private $blocked;

    /**
     * @return mixed
     */
    public function getHashName()
    {
        return $this->hashName;
    }

    /**
     * @param mixed $hashName
     * @return InstagramHashes
     */
    public function setHashName($hashName)
    {
        $this->hashName = $hashName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHashId()
    {
        return $this->hashId;
    }

    /**
     * @param mixed $hashId
     * @return InstagramHashes
     */
    public function setHashId($hashId)
    {
        $this->hashId = $hashId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastCheck()
    {
        return $this->lastCheck;
    }

    /**
     * @param mixed $lastCheck
     * @return InstagramHashes
     */
    public function setLastCheck($lastCheck)
    {
        $this->lastCheck = $lastCheck;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumberOfChecks()
    {
        return $this->numberOfChecks;
    }

    /**
     * @param mixed $numberOfChecks
     * @return InstagramHashes
     */
    public function setNumberOfChecks($numberOfChecks)
    {
        $this->numberOfChecks = $numberOfChecks;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPermanentlyBlocked()
    {
        return $this->permanentlyBlocked;
    }

    /**
     * @param mixed $permanentlyBlocked
     * @return InstagramHashes
     */
    public function setPermanentlyBlocked($permanentlyBlocked)
    {
        $this->permanentlyBlocked = $permanentlyBlocked;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBlocked()
    {
        return $this->blocked;
    }

    /**
     * @param mixed $blocked
     * @return InstagramHashes
     */
    public function setBlocked($blocked)
    {
        $this->blocked = $blocked;
        return $this;
    }



}
