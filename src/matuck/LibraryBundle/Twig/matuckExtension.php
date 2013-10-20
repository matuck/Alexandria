<?php
namespace matuck\LibraryBundle\Twig;

use matuck\LibraryBundle\Lib\Filehandler\Filehandler;
use FPN\TagBundle\Entity\TagManager;

class matuckExtension extends \Twig_Extension
{
    private $fh;
    protected $fpntag;
    protected $nsfwtags;

    public function __construct(Filehandler $fh, TagManager $fpntag,$nsfwtags)
    {
        $this->fh = $fh;
        $this->fpntag = $fpntag;
        $this->nsfwtags = $nsfwtags;
    }
    
    public function getFilters()
    {
        return array(
            'numbertotext' => new \Twig_Filter_Method($this, 'numbertotextFilter'),
        );
    }
    
    public function getFunctions()
    {
        return array(
            'coverurl' => new \Twig_Function_Method($this, 'coverurl'),
            'nsfw' => new \Twig_Function_Method($this, 'nsfw'),
        );
    }

    public function numbertotextFilter($number)
    {
        if($number >=0 && $number < 100)
        {
            $result = $number;
        }
        else if ($number >= 100 && $number <= 999)
        {
            $result =substr($number, 0, -2) . " Hundred";
        }
        else if ($number >= 1000 && $number <= 999999)
        {
            $result =substr($number, 0, -3) . " Thousand";
        }
        else if ($number >= 1000000 && $number <= 999999999)
        {
            $first =substr($number, 0, -6);
            $second =substr($number, 1, -4);
            $result = $first . "." . $second . " Million";
        }
        return $result;
    }
    
    public function coverurl($id)
    {
        return $this->fh->getCover($id);
    }
    
    public function nsfw($book)
    {
        $tags = array();
        if($book instanceof \matuck\LibraryBundle\Entity\Book)
        {
            $this->fpntag->loadTagging($book);
            $booktags = $book->getTags();
            foreach($booktags as $booktag)
            {
                /* @var $booktag \matuck\LibraryBundle\Entity\Tag */
                $tags[] = $booktag->getName();
            }
        }
        else if($book instanceof \Zend\Search\Lucene\Search\QueryHit)
        {
            $tags = unserialize($book->tags);
            
        }
        else
        {
            throw new Exception(sprintf('The object of type %s is not supported. Please pass in either a Book entity or a QueryHit', get_class($book)));
        }
        
        $tags = array_map('strtolower', $tags);
        foreach($this->nsfwtags as $tocheck)
        {
            if(in_array(strtolower($tocheck), $tags))
            {
                return 'nsfw';
            }
        }
    }

    public function getName()
    {
        return 'matuck_extension';
    }
}
?>