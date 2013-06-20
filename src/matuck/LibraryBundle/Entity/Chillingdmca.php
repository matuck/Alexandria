<?php

namespace matuck\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Chillingdmca
 *
 * @ORM\Table(name="chillingdmca")
 * @ORM\Entity(repositoryClass="matuck\LibraryBundle\Entity\ChillingdmcaRepository")
 */
class Chillingdmca
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
     * @var string
     *
     * @ORM\Column(name="book_title", type="string", length=255, nullable=true)
     */
    private $bookTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="book_author", type="string", length=255, nullable=true)
     */
    private $bookAuthor;

    /**
     * @var string
     *
     * @ORM\Column(name="dmca_name", type="string", length=255, nullable=true)
     */
    private $dmcaName;

    /**
     * @var string
     *
     * @ORM\Column(name="dmca_email", type="string", length=255, nullable=true)
     */
    private $dmcaEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="ip_address", type="string", length=255, nullable=true)
     */
    private $ipAddress;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    private $updatedAt;



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
     * Set bookTitle
     *
     * @param string $bookTitle
     * @return Chillingdmca
     */
    public function setBookTitle($bookTitle)
    {
        $this->bookTitle = $bookTitle;
    
        return $this;
    }

    /**
     * Get bookTitle
     *
     * @return string 
     */
    public function getBookTitle()
    {
        return $this->bookTitle;
    }

    /**
     * Set bookAuthor
     *
     * @param string $bookAuthor
     * @return Chillingdmca
     */
    public function setBookAuthor($bookAuthor)
    {
        $this->bookAuthor = $bookAuthor;
    
        return $this;
    }

    /**
     * Get bookAuthor
     *
     * @return string 
     */
    public function getBookAuthor()
    {
        return $this->bookAuthor;
    }

    /**
     * Set dmcaName
     *
     * @param string $dmcaName
     * @return Chillingdmca
     */
    public function setDmcaName($dmcaName)
    {
        $this->dmcaName = $dmcaName;
    
        return $this;
    }

    /**
     * Get dmcaName
     *
     * @return string 
     */
    public function getDmcaName()
    {
        return $this->dmcaName;
    }

    /**
     * Set dmcaEmail
     *
     * @param string $dmcaEmail
     * @return Chillingdmca
     */
    public function setDmcaEmail($dmcaEmail)
    {
        $this->dmcaEmail = $dmcaEmail;
    
        return $this;
    }

    /**
     * Get dmcaEmail
     *
     * @return string 
     */
    public function getDmcaEmail()
    {
        return $this->dmcaEmail;
    }

    /**
     * Set ipAddress
     *
     * @param string $ipAddress
     * @return Chillingdmca
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    
        return $this;
    }

    /**
     * Get ipAddress
     *
     * @return string 
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Chillingdmca
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Chillingdmca
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}