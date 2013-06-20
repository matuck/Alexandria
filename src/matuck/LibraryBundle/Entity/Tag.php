<?php
namespace matuck\LibraryBundle\Entity;

use \FPN\TagBundle\Entity\Tag as BaseTag;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * matuck\LibraryBundle\Entity\Tag
 *
 * @ORM\Table(name="tag")
 * @ORM\Entity(repositoryClass="matuck\LibraryBundle\Entity\TagRepository")
 */
class Tag extends BaseTag
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Tagging", mappedBy="tag", fetch="EAGER")
     **/
    protected $tagging;
 
    /** 
     * @Gedmo\Slug(fields={"name"})
     */
    protected $slug;
    
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
     * Add tagging
     *
     * @param \matuck\LibraryBundle\Entity\Tagging $tagging
     * @return Tag
     */
    public function addTagging(\matuck\LibraryBundle\Entity\Tagging $tagging)
    {
        $this->tagging[] = $tagging;
    
        return $this;
    }

    /**
     * Remove tagging
     *
     * @param \matuck\LibraryBundle\Entity\Tagging $tagging
     */
    public function removeTagging(\matuck\LibraryBundle\Entity\Tagging $tagging)
    {
        $this->tagging->removeElement($tagging);
    }

    /**
     * Get tagging
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTagging()
    {
        return $this->tagging;
    }
}