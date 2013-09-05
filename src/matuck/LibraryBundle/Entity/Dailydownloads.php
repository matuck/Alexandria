<?php

namespace matuck\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dailydownloads
 *
 * @ORM\Table(name="dailydownloads")
 * @ORM\Entity(repositoryClass="matuck\LibraryBundle\Entity\DailydownloadsRepository")
 */
class Dailydownloads
{
    /**
     * @var integer
     * 
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="day", type="datetime", nullable=false)
     */
    private $day;

    /**
     * @var Integer
     *
     * @ORM\Column(name="downloads", type="bigint", nullable=true)
     */
    private $downloads;


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
     * Set day
     *
     * @param \DateTime $day
     * @return Dailydownloads
     */
    public function setDay($day)
    {
        $this->day = $day;
    
        return $this;
    }

    /**
     * Get day
     *
     * @return \DateTime 
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Set downloads
     *
     * @param integer $downloads
     * @return Dailydownloads
     */
    public function setDownloads($downloads)
    {
        $this->downloads = $downloads;
    
        return $this;
    }

    /**
     * Get downloads
     *
     * @return integer 
     */
    public function getDownloads()
    {
        return $this->downloads;
    }
}
