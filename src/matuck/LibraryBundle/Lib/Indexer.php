<?php
namespace matuck\LibraryBundle\Lib;

use matuck\LibraryBundle\Entity\Author;
use matuck\LibraryBundle\Entity\Book;
use matuck\LibraryBundle\Entity\Serie;
use matuck\LibraryBundle\Entity\Tag;
use FPN\TagBundle\Entity\TagManager;
use Ivory\LuceneSearchBundle\Model\LuceneManager;
use Ivory\LuceneSearchBundle\Model\Document;
use Ivory\LuceneSearchBundle\Model\Field;

class Indexer
{
    protected $index;
    protected $tagmanager;

    public function __construct(LuceneManager $index, TagManager $tagmanager)
    {
        $this->index = $index->getIndex('master');
        $this->tagmanager = $tagmanager;
    }
    
    public function indexAuthor(Author $author)
    {
        $doc = new Document();
        $doc->addField(Field::keyword('type', 'author'));
        $doc->addField(Field::binary('objid', $author->getId()));
        $doc->addField(Field::text('name', $author->getName()));
        $doc->addField(Field::text('bio', $author->getBiography()));
        $this->index->addDocument($doc);
    }
    
    public function deleteAuthor(Author $author)
    {
        $results = $this->index->find('type:author AND name:"'.$author->getName().'"');
        foreach($results as $doc)
        {
            /* @var $doc Document */
            if($author->getId() == $doc->objid && $doc->type == 'author')
            {
                $this->index->delete($doc->id);
            }
        }
    }
    
    public function indexBook(Book $book)
    {
        $this->tagmanager->loadTagging($book);
        $booktags = $book->getTags();
        $tags = array();
        foreach($booktags as $booktag)
        {
            /* @var $booktag Tag */
            $tags[] = $booktag->getName();
        }
        $doc = new Document();
        $doc->addField(Field::keyword('type', 'book'));
        $doc->addField(Field::binary('objid', $book->getId()));
        $doc->addField(Field::text('title', $book->getTitle()));
        $doc->addField(Field::text('author', $book->getAuthor()->getName()));
        $doc->addField(Field::binary('authorid', $book->getAuthor()->getId()));
        if($series = $book->getSerie())
        {
            $doc->addField(Field::text('series', $series->getName()));
            $doc->addField(Field::binary('serieid', $series->getId()));
            $doc->addField(Field::unIndexed('serieNbr', $book->getSerieNbr()));
        }
        $doc->addField(Field::unIndexed('summary', $book->getSummary()));
        $doc->addField(Field::unIndexed('tags', serialize($tags)));
        $this->index->addDocument($doc);
    }
    
    public function deleteBook(Book $book)
    {
        
        $results = $this->index->find('type:book AND title:"'.$book->getTitle().'" AND author:"'.$book->getAuthor()->getName().'"');
        foreach($results as $doc)
        {
            /* @var $doc Document */
            if($book->getId() == $doc->objid && $doc->type == 'book')
            {
                $this->index->delete($doc->id);
            }
        }
    }
    
    public function indexSeries(Serie $series)
    {
        $doc = new Document();
        $doc->addField(Field::keyword('type', 'serie'));
        $doc->addField(Field::binary('objid', $series->getId()));
        $doc->addField(Field::text('name', $series->getName()));
        $this->index->addDocument($doc);
    }
    
    public function deleteSeries(Serie $series)
    {
        $results = $this->index->find('type:serie AND name:"'.$series->getName().'"');
        foreach($results as $doc)
        {
            /* @var $doc Document */
            if($series->getId() == $doc->objid && $doc->type == 'serie')
            {
                $this->index->delete($doc->id);
            }
        }
    }
    
    public function commit()
    {
        $this->index->commit();
    }
    
    public function optimize()
    {
        $this->index->optimize();
    }
    
}