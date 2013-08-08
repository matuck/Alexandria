<?php

namespace matuck\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Authorvotes
 *
 * @ORM\Table(name="authorvotes")
 * @ORM\Entity(repositoryClass="matuck\LibraryBundle\Entity\AuthorvotesRepository")
 */
class Authorvotes
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
     * @ORM\Column(name="iphash", type="string", length=255, nullable=false)
     */
    private $iphash;

    /**
     * @var \Author
     *
     * @ORM\ManyToOne(targetEntity="Author", inversedBy="authorvotes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="authorid", referencedColumnName="id")
     * })
     */
    private $authorid;



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
     * Set iphash
     *
     * @param string $iphash
     * @return Authorvotes
     */
    public function setIphash($iphash)
    {
        $this->iphash = $iphash;
    
        return $this;
    }

    /**
     * Get iphash
     *
     * @return string 
     */
    public function getIphash()
    {
        return $this->iphash;
    }

    /**
     * Set authorid
     *
     * @param \matuck\LibraryBundle\Entity\Author $authorid
     * @return Authorvotes
     */
    public function setAuthorid(\matuck\LibraryBundle\Entity\Author $authorid = null)
    {
        $this->authorid = $authorid;
    
        return $this;
    }

    /**
     * Get authorid
     *
     * @return \matuck\LibraryBundle\Entity\Author 
     */
    public function getAuthorid()
    {
        return $this->authorid;
    }
}