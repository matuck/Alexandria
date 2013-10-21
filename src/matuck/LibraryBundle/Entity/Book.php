<?php

namespace matuck\LibraryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DoctrineExtensions\Taggable\Taggable;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Book
 *
 * @ORM\Table(name="book")
 * @ORM\Entity(repositoryClass="matuck\LibraryBundle\Entity\BookRepository")
 */
class Book implements Taggable
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
     * @ORM\Column(name="isbn", type="string", length=20, nullable=true)
     */
    private $isbn;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="summary", type="text", nullable=true)
     */
    private $summary;

    /**
     * @var string
     *
     * @ORM\Column(name="serie_nbr", type="string", length=10, nullable=true)
     */
    private $serieNbr;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_public", type="boolean", nullable=false)
     */
    private $isPublic;

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
     * @var Integer
     *
     * @ORM\Column(name="rated", type="bigint", nullable=true)
     */
    private $rated;

    /**
     * @var \Author
     *
     * @ORM\ManyToOne(targetEntity="Author", inversedBy="books")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     * })
     */
    private $author;

    /**
     * @var \Serie
     *
     * @ORM\ManyToOne(targetEntity="Serie", inversedBy="books", cascade={"all"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="serie_id", referencedColumnName="id")
     * })
     */
    private $serie;

    /**
     * @ORM\OneToMany(targetEntity="Flags", mappedBy="book", cascade={"all"})
     */
    private $flags;

    /**
     * @ORM\OneToMany(targetEntity="Download", mappedBy="book", cascade={"all"})
     */
    private $downloads;
    
    private $tags;
    
    /**
     * @ORM\OneToOne(targetEntity="Featured", cascade={"all"})
     */
    private $featured;
            
    /**
     * @var integer
     *
     * @ORM\Column(name="downcount", type="bigint", nullable=true)
     */
    private $downcount;
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
     * Set isbn
     *
     * @param string $isbn
     * @return Book
     */
    public function setIsbn($isbn)
    {
        $this->isbn = $isbn;
    
        return $this;
    }

    /**
     * Get isbn
     *
     * @return string 
     */
    public function getIsbn()
    {
        return $this->isbn;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Book
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set summary
     *
     * @param string $summary
     * @return Book
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    
        return $this;
    }

    /**
     * Get summary
     *
     * @return string 
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set serieNbr
     *
     * @param integer $serieNbr
     * @return Book
     */
    public function setSerieNbr($serieNbr)
    {
        $this->serieNbr = $serieNbr;
    
        return $this;
    }

    /**
     * Get serieNbr
     *
     * @return integer 
     */
    public function getSerieNbr()
    {
        return $this->serieNbr;
    }

    /**
     * Set isPublic
     *
     * @param boolean $isPublic
     * @return Book
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    
        return $this;
    }

    /**
     * Get isPublic
     *
     * @return boolean 
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Book
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
     * @return Book
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

    /**
     * Set rated
     *
     * @param boolean $rated
     * @return Book
     */
    public function setRated($rated)
    {
        $this->rated = $rated;
    
        return $this;
    }

    /**
     * Get rated
     *
     * @return int 
     */
    public function getRated()
    {
        return $this->rated;
    }

    /**
     * Set author
     *
     * @param \matuck\LibraryBundle\Entity\Author $author
     * @return Book
     */
    public function setAuthor(\matuck\LibraryBundle\Entity\Author $author = null)
    {
        $this->author = $author;
    
        return $this;
    }

    /**
     * Get author
     *
     * @return \matuck\LibraryBundle\Entity\Author 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set serie
     *
     * @param \matuck\LibraryBundle\Entity\Serie $serie
     * @return Book
     */
    public function setSerie(\matuck\LibraryBundle\Entity\Serie $serie = null)
    {
        $this->serie = $serie;
    
        return $this;
    }

    /**
     * Get serie
     *
     * @return \matuck\LibraryBundle\Entity\Serie 
     */
    public function getSerie()
    {
        return $this->serie;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->flags = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add flags
     *
     * @param \matuck\LibraryBundle\Entity\Flags $flags
     * @return Book
     */
    public function addFlag(\matuck\LibraryBundle\Entity\Flags $flags)
    {
        $this->flags[] = $flags;
    
        return $this;
    }

    /**
     * Remove flags
     *
     * @param \matuck\LibraryBundle\Entity\Flags $flags
     */
    public function removeFlag(\matuck\LibraryBundle\Entity\Flags $flags)
    {
        $this->flags->removeElement($flags);
    }

    /**
     * Get flags
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Add downloads
     *
     * @param \matuck\LibraryBundle\Entity\Download $downloads
     * @return Book
     */
    public function addDownload(\matuck\LibraryBundle\Entity\Download $downloads)
    {
        $this->downloads[] = $downloads;
    
        return $this;
    }

    /**
     * Remove downloads
     *
     * @param \matuck\LibraryBundle\Entity\Download $downloads
     */
    public function removeDownload(\matuck\LibraryBundle\Entity\Download $downloads)
    {
        $this->downloads->removeElement($downloads);
    }

    /**
     * Get downloads
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDownloads()
    {
        return $this->downloads;
    }
    
    public function getDownloadName()
    {
        $downloadName=$this->getAuthor()->getName()."-".$this->title;
        $downloadName=preg_replace("/'/",'',$downloadName);
        $downloadName=preg_replace("/,/",'_',$downloadName);
        $downloadName=preg_replace("/ /",'_',$downloadName);
        $downloadName=preg_replace("/\(/",'',$downloadName);
        $downloadName=preg_replace("/\)/",'',$downloadName);
        $downloadName=preg_replace("/\]/",'',$downloadName);
        $downloadName=preg_replace("/\[/",'',$downloadName);
        $downloadName=preg_replace("/\./",'_',$downloadName);

        $downloadName=preg_replace("/_+/",'_',$downloadName);
        $downloadName=str_replace('"', '_', $downloadName);
        $downloadName=str_replace('*', '_', $downloadName);
        $downloadName=str_replace('/', '_', $downloadName);
        $downloadName=str_replace(':', '_', $downloadName);
        $downloadName=str_replace('<', '_', $downloadName);
        $downloadName=str_replace('>', '_', $downloadName);
        $downloadName=str_replace('?', '_', $downloadName);
        $downloadName=str_replace('\\', '_', $downloadName);
        $downloadName=str_replace('|', '_', $downloadName);
        return $downloadName;
    }
    
    public function getTags()
    {
        $this->tags = $this->tags ?: new ArrayCollection();

        return $this->tags;
    }

    public function getTaggableType()
    {
        return 'book';
    }

    public function getTaggableId()
    {
        return $this->getId();
    }

    /**
     * Set featured
     *
     * @param \matuck\LibraryBundle\Entity\Featured $featured
     * @return Book
     */
    public function setFeatured(\matuck\LibraryBundle\Entity\Featured $featured = null)
    {
        $this->featured = $featured;
    
        return $this;
    }

    /**
     * Get featured
     *
     * @return \matuck\LibraryBundle\Entity\Featured 
     */
    public function getFeatured()
    {
        return $this->featured;
    }

    /**
     * Set downcount
     *
     * @param integer $downcount
     * @return Book
     */
    public function setDowncount($downcount)
    {
        $this->downcount = $downcount;
    
        return $this;
    }

    /**
     * Get downcount
     *
     * @return integer 
     */
    public function getDowncount()
    {
        return $this->downcount;
    }
}