<?php

namespace matuck\LibraryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use matuck\LibraryBundle\Entity\Rating;
use matuck\LibraryBundle\Entity\Book;
use matuck\LibraryBundle\Form\BookType;
use Ivory\LuceneSearchBundle\Model\Document;
use Ivory\LuceneSearchBundle\Model\Field;

class BookController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $books = $em->getRepository('matuckLibraryBundle:Book')->findAll();
        
        return $this->render('matuckLibraryBundle:Book:index.html.twig', array('books' => $books));
    }
    
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if(!$book = $em->getRepository('matuckLibraryBundle:Book')->find($id))
        {
            throw $this->createNotFoundException("The book you requested could not be found");
        }
        $searchTerm= $book->getAuthor()->getName()." ".$book->getTitle();
        $searchTerm=preg_replace('/,/','',$searchTerm);
        $author = $book->getAuthor()->getName();
        $title = $book->getTitle();
        $amazonUrl="http://www.bookspook.com/search?group=".urlencode($author)."&title=".urlencode($title)."&feelinglucky=1";
        $audibleUrl="http://www.qksrv.net/click-5758986-10273919?url=http://www.audible.com/search?advsearchKeywords=".urlencode($searchTerm)."&source_code=COMA0213WS031709";
        
        $tagManager = $this->get('fpn_tag.tag_manager');
        $tagManager->loadTagging($book);
        return $this->render('matuckLibraryBundle:Book:show.html.twig', array('book' => $book, 'amazonUrl' => $amazonUrl, 'audibleUrl' => $audibleUrl));
    }
    
    public function tagaddAction()
    {
        $formdata = $this->getRequest()->get('form');
        $em = $this->getDoctrine()->getEntityManager();
        $tagManager = $this->get('fpn_tag.tag_manager');
        /* @var $tagManager \FPN\TagBundle\Entity\TagManager */
        $book = $em->getRepository('matuckLibraryBundle:Book')->find($formdata['book_id']);
        $tagManager->loadTagging($book);
        /* @var $book Book */
        if($formdata['tags'] != NULL && $formdata['tags'] != '')
        {
            $tags = str_getcsv($formdata['tags'],',');
            $tags = array_map('trim', $tags);
            $tags = $tagManager->loadOrCreateTags($tags);
            $tagManager->addTags($tags, $book);
        }
        if(isset($formdata['removetag']))
        {
            $removetags = array();
            foreach($formdata['removetag'] as $tag)
            {
                $tagManager->removeTag($tagManager->loadOrCreateTag($tag), $book);
            }
        }

        $tagManager->saveTagging($book);
        return $this->redirect($this->generateUrl('matuck_library_book_show', array('id' => $formdata['book_id'])));
    }
    
    public function newAction()
    {
        $entity = new Book();
        $form   = $this->createForm(new BookType(), $entity);

        return $this->render('matuckLibraryBundle:Book:new.html.twig', array(
            'book' => $entity,
            'form'   => $form->createView(),
        ));
    }

    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if(!$book = $em->getRepository('matuckLibraryBundle:Book')->find($id))
        {
            throw $this->createNotFoundException("The book you requested could not be found");
        }
        $form   = $this->createForm(new BookType(), $book);
        $form->remove('tags');
        return $this->render('matuckLibraryBundle:Book:edit.html.twig', array(
            'book' => $book,
            'form'   => $form->createView(),
        ));
    }
    
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        
        if($this->getRequest()->getMethod() != 'POST')
        {
            throw $this->createNotFoundException("The pages does not exist in this context");
        }
        if(!$book = $em->getRepository('matuckLibraryBundle:Book')->find($id))
        {
            throw $this->createNotFoundException("The book you requested could not be found");
        }
        $title = $book->getTitle();
        $author = $book->getAuthor()->getName();
        $form = $this->createForm(new BookType(), $book);
        $form->remove('tags');
        $form->bind($this->getRequest());
        if($form->isValid())
        {
            $em->persist($book);
            $em->flush();
            $index = $this->get('ivory_lucene_search')->getIndex('master');
            /* @var $index \Zend\Search\Lucene\Index */
            $results = $index->find('type:book AND title:"'.$title.'" AND author:"'.$author.'"');
            foreach($results as $doc)
            {
                /* @var $doc Document */
                if($book->getId() == $doc->objid && $doc->type == 'book')
                {
                    $index->delete($doc->id);
                    $index->commit();
                    $document = new Document();
                    $document->addField(Field::keyword('type', 'book'));
                    $document->addField(Field::binary('objid', $book->getId()));
                    $document->addField(Field::text('title', $book->getTitle()));
                    $document->addField(Field::text('author', $book->getAuthor()->getName()));
                    $document->addField(Field::binary('authorid', $book->getAuthor()->getId()));
                    if($series = $book->getSerie())
                    {
                        $document->addField(Field::text('series', $series->getName()));
                        $document->addField(Field::binary('serieid', $series->getId()));
                        $document->addField(Field::unIndexed('serieNbr', $book->getSerieNbr()));
                    }
                    $document->addField(Field::unIndexed('summary', $book->getSummary()));
                    $index->addDocument($document);
                    $index->commit();
                }
            }
            return $this->redirect($this->generateUrl('matuck_library_book_show', array('id' => $id)));
        }

        return $this->render('matuckLibraryBundle:Book:edit.html.twig', array(
            'book' => $book,
            'form'   => $form->createView(),
        ));
    }
    
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if(!$book = $em->getRepository('matuckLibraryBundle:Book')->find($id))
        {
            throw $this->createNotFoundException("The book you requested could not be found");
        }
        $title = $book->getTitle();
        $author = $book->getAuthor()->getName();
        $ratings = $em->getRepository('matuckLibraryBundle:Rating')->findByBook($book);
        
        foreach($ratings as $rating)
        {
            $em->remove($rating);
        }
        $tagManager = $this->get('fpn_tag.tag_manager');
        /* @var $tagManager \FPN\TagBundle\Entity\TagManager */
        $tagManager->deleteTagging($book);
        $em->remove($book);
        ////delete book files
        $fh = $this->get('matuck_library.filehandler');
        /* @var $fh \matuck\LibraryBundle\Lib\Filehandler\Filehandler */
        $fh->deleteBook($book->getId());
        $fh->deleteCover($book->getId());
        
        $em->flush();
        $index = $this->get('ivory_lucene_search')->getIndex('master');
        /* @var $index \Zend\Search\Lucene\Index */
        $results = $index->find('type:book AND title:"'.$title.'" AND author:"'.$author.'"');
        foreach($results as $doc)
        {
            /* @var $doc Document */
            if($book->getId() == $doc->objid && $doc->type == 'book')
            {
                $index->delete($doc->id);
                $index->commit();
            }
        }
        return $this->redirect($this->generateUrl('matuck_library_homepage'));
    }
  
    public function ratingAction($id, $rating)
    {
        $iphash = $this->get('matuck_library.iphash')->get();
        $em = $this->getDoctrine()->getManager();
        $oldrate = $em->getRepository('matuckLibraryBundle:Rating')->findByBookandIpHash($id, $iphash);
        //delete old rating
        if($oldrate)
        {
            $em->remove($oldrate);
            $em->flush();
        }
        $book = $em->getRepository('matuckLibraryBundle:Book')->find($id);
        $bookrate = new Rating();
        $bookrate->setBookid($book);
        $bookrate->setIphash($iphash);
        $bookrate->setRating($rating);
        try
        {
          //save new rating
          $em->persist($bookrate);
          $em->flush();
          $bookratings = $em->getRepository('matuckLibraryBundle:Rating')->findByBook($book);
          $ratingcount = count($bookratings);
          $totalrate = 0;
          foreach ($bookratings as $rate)
          {
            $totalrate = $totalrate + $rate->getRating();
          }
          if($ratingcount > 0)
          {
            $book->setRated(round($totalrate / $ratingcount));
            //save book with new rating calculated
            $em->persist($book);
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'You have successfully rated this book.');
            return $this->redirect($this->generateUrl('matuck_library_book_show', array('id' => $id)));
          }
          else
          {
            $this->rate = 0;
            $this->get('session')->getFlashBag()->add('error', 'Failed to save your rating!');
            return $this->redirect($this->generateUrl('matuck_library_book_show', array('id' => $id)));
          }
        }
        catch (Exception $e)
        {
            $this->get('session')->getFlashBag()->add('error', 'Failed to save your rating!');
            return $this->redirect($this->generateUrl('matuck_library_book_show', array('id' => $id)));
        }
    }
    
    public function newbooksAction()
    {
        $em = $this->getDoctrine()->getManager();
        $pager = $em->getRepository('matuckLibraryBundle:Book')->newestbooks();
        if($this->getRequest()->get('page'))
        {
            $pager->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $pager->setCurrentPage(1);
        }
        return $this->render('matuckLibraryBundle:Book:newbook.html.twig', array('pager' => $pager));
    }
    
    public function popularAction()
    {
        $em = $this->getDoctrine()->getManager();
        $pager = $em->getRepository('matuckLibraryBundle:Book')->popularbooks();
        if($this->getRequest()->get('page'))
        {
            $pager->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $pager->setCurrentPage(1);
        }
        return $this->render('matuckLibraryBundle:Book:popular.html.twig', array('pager' => $pager));
    }
}
