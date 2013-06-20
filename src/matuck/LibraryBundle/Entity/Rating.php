<?php

namespace matuck\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rating
 *
 * @ORM\Table(name="rating")
 * @ORM\Entity(repositoryClass="matuck\LibraryBundle\Entity\RatingRepository")
 */
class Rating
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
     * @var boolean
     *
     * @ORM\Column(name="rating", type="boolean", nullable=false)
     */
    private $rating;

    /**
     * @var \Book
     *
     * @ORM\ManyToOne(targetEntity="Book")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="bookid", referencedColumnName="id")
     * })
     */
    private $bookid;



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
     * @return Rating
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
     * Set rating
     *
     * @param boolean $rating
     * @return Rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    
        return $this;
    }

    /**
     * Get rating
     *
     * @return boolean 
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set bookid
     *
     * @param \matuck\LibraryBundle\Entity\Book $bookid
     * @return Rating
     */
    public function setBookid(\matuck\LibraryBundle\Entity\Book $bookid = null)
    {
        $this->bookid = $bookid;
    
        return $this;
    }

    /**
     * Get bookid
     *
     * @return \matuck\LibraryBundle\Entity\Book 
     */
    public function getBookid()
    {
        return $this->bookid;
    }
}