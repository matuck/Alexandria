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
        
        $response = $this->render('matuckLibraryBundle:Book:index.html.twig', array('books' => $books));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
        
    }
    
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if(!$book = $em->getRepository('matuckLibraryBundle:Book')->find($id))
        {
            throw $this->createNotFoundException("The book you requested could not be found");
        }
        if(!$book->getIsPublic())
        {
            $response =  $this->render('matuckLibraryBundle:Pages:dmca.html.twig');
            $response->setPublic();
            $response->setSharedMaxAge($this->container->getParameter('cache_time'));
            return $response;
        }
        $searchTerm= $book->getAuthor()->getName()." ".$book->getTitle();
        $searchTerm=preg_replace('/,/','',$searchTerm);
        $author = $book->getAuthor()->getName();
        $title = $book->getTitle();
        $amazonUrl="http://www.bookspook.com/search?group=".urlencode($author)."&title=".urlencode($title)."&feelinglucky=1";
        $audibleUrl="http://www.qksrv.net/click-5758986-10273919?url=http://www.audible.com/search?advsearchKeywords=".urlencode($searchTerm)."&source_code=COMA0213WS031709";
        
        $tagManager = $this->get('fpn_tag.tag_manager');
        $tagManager->loadTagging($book);
        
        $form = $this->buildtagform($book);
        $response =  $this->render('matuckLibraryBundle:Book:show.html.twig', array('book' => $book, 'tagform' => $form->getForm()->createView(), 'amazonUrl' => $amazonUrl, 'audibleUrl' => $audibleUrl));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    private function buildtagform(Book $book)
    {
        $form = $this->createFormBuilder();
        $form->add('book_id', 'hidden', array('data' => $book->getId()));
        if($this->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            $choices = array();
            foreach($book->getTags() as $tag)
            {
                $choices[$tag->getId()] = $tag->getName();
            }
            $form->add('removetags', 'choice', array('choices' => $choices, 'multiple' => true, 'expanded' => true,'required' => false));
        }
        $form->add('tags', 'text', array('required' => false));
        if($this->container->getParameter('matuck_library_usecaptchas'))
        {
            $form->add('captcha', 'captcha');
        }
        return $form;
    }
    public function tagaddAction()
    {
        $formdata = $this->getRequest()->get('form');
        print_r($formdata);
        $em = $this->getDoctrine()->getEntityManager();
        $tagManager = $this->get('fpn_tag.tag_manager');
        /* @var $tagManager \FPN\TagBundle\Entity\TagManager */
        
        if(!$book = $em->getRepository('matuckLibraryBundle:Book')->find($formdata['book_id']))
        {
            throw new \Doctrine\ORM\EntityNotFoundException();
        }
        
        $indexer = $this->get('matuck_library.searchindexer');
        /* @var $indexer \matuck\LibraryBundle\Lib\Indexer */
        
        $indexer->deleteBook($book);
        
        $tagManager->loadTagging($book);
        /* @var $book Book */
        if($formdata['tags'] != NULL && $formdata['tags'] != '')
        {
            $tags = str_getcsv($formdata['tags'],',');
            $tags = array_map('trim', $tags);
            $tags = $tagManager->loadOrCreateTags($tags);
            $tagManager->addTags($tags, $book);
        }
        $tagrepo = $em->getRepository('matuckLibraryBundle:Tag');
        
        if(isset($formdata['removetags']))
        {
            $removetags = array();
            foreach($formdata['removetags'] as $tag)
            {
                $tag2 = $tagrepo->find($tag);
                $tagManager->removeTag($tag2, $book);
            }
        }

        $tagManager->saveTagging($book);
        $indexer->indexBook($book);
        return $this->redirect($this->generateUrl('matuck_library_book_show', array('id' => $formdata['book_id'])));
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
        $form->add('newcover', 'file', array("required" => false, "mapped" => false, 'label' => 'Replacement Cover'));
        $form->add('newfile', 'file', array("required" => false, "mapped" => false, 'label' => 'Replacement Epub'));
        if($this->container->getParameter('matuck_library_usecaptchas'))
        {
            $form->add('captcha', 'captcha');
        }
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
        
        $indexer = $this->get('matuck_library.searchindexer');
        /* @var $indexer \matuck\LibraryBundle\Lib\Indexer */
        $indexer->deleteBook($book);
        $form = $this->createForm(new BookType(), $book);
        $form->remove('tags');
        $form->add('newcover', 'file', array("required" => false,"mapped" => false, 'label' => 'Replacement Cover'));
        $form->add('newfile', 'file', array("required" => false, "mapped" => false, 'label' => 'Replacement Epub'));
        if($this->container->getParameter('matuck_library_usecaptchas'))
        {
            $form->add('captcha', 'captcha');
        }
        $form->bind($this->getRequest());
        if($form->isValid())
        {
            $em->persist($book);
            $em->flush();
            
            $fh = $this->get('matuck_library.filehandler');
            $tmpuploads = $this->container->getParameter('matuck_library_tempuploads');
            $tmpid = uniqid();
            /* @var $fh \matuck\LibraryBundle\Lib\Filehandler\Filehandler */
            if($newcover = $this->getRequest()->files->get('matuck_librarybundle_booktype')['newcover'])
            {
                /* @var $newcover \Symfony\Component\HttpFoundation\File\UploadedFile */
                $newcover->move($tmpuploads, $tmpid.'.cover');
                $fh->moveCover($tmpid.'.cover', $book->getId());
            }
            
            if($newfile = $this->getRequest()->files->get('matuck_librarybundle_booktype')['newfile'])
            {
                /* @var $newfile \Symfony\Component\HttpFoundation\File\UploadedFile */
                $newfile->move($tmpuploads, $tmpid);
                $fh->moveBook($tmpid, $book->getId());
            }
            $indexer->deleteBook($book);
            return $this->redirect($this->generateUrl('matuck_library_book_show', array('id' => $id)));
        }
        
        $indexer->indexBook($book);
        return $this->render('matuckLibraryBundle:Book:edit.html.twig', array(
            'book' => $book,
            'form'   => $form->createView(),
        ));
    }
    
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        if(!$book = $em->getRepository('matuckLibraryBundle:Book')->find($id))
        {
            throw $this->createNotFoundException("The book you requested could not be found");
        }
        $title = $book->getTitle();
        $author = $book->getAuthor()->getName();
        
        $ratings = $em->getRepository('matuckLibraryBundle:Rating')->findByBook($book);
        
        foreach($ratings as $rating)
        {
            echo $rating->getRating();
            $em->remove($rating);
        }
        $em->flush();
        $tagManager = $this->get('fpn_tag.tag_manager');
        /* @var $tagManager \FPN\TagBundle\Entity\TagManager */
        $tagManager->deleteTagging($book);
        
        $indexer = $this->get('matuck_library.searchindexer');
        /* @var $indexer \matuck\LibraryBundle\Lib\Indexer */
        $indexer->deleteBook($book);
        
        $em->remove($book);
        ////delete book files
        $fh = $this->get('matuck_library.filehandler');
        /* @var $fh \matuck\LibraryBundle\Lib\Filehandler\Filehandler */
        $fh->deleteBook($book->getId());
        $fh->deleteCover($book->getId());
        
        $em->flush();
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
        $response = $this->render('matuckLibraryBundle:Book:newbook.html.twig', array('pager' => $pager));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
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
        $response = $this->render('matuckLibraryBundle:Book:popular.html.twig', array('pager' => $pager));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
}
