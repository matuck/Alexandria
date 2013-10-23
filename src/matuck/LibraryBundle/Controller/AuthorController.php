<?php

namespace matuck\LibraryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use matuck\LibraryBundle\Form\AuthorType;
use matuck\LibraryBundle\Form\mergeAuthorType;
use matuck\LibraryBundle\Entity\Author;
use matuck\LibraryBundle\Entity\Authorvotes;

class AuthorController extends Controller
{
    public function indexAction(sfWebRequest $request)
    {
        return $this->createNotFoundException();
    }
    
    public function bioAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if(!$author = $em->getRepository('matuckLibraryBundle:Author')->find($id))
        {
            throw $this->createNotFoundException("The author you requested could not be found");
        }
        $form   = $this->createForm(new AuthorType, $author);
        $form->remove('createdAt');
        $form->remove('updatedAt');
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            $form->remove('name');
        }
        $response = $this->render('matuckLibraryBundle:Author:bio.html.twig', array(
            'author' => $author,
            'form'   => $form->createView(),
        ));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    public function bioupdateAction($id)
    {
        if(!$this->getRequest()->getMethod() == 'POST')
        {
            throw $this->createNotFoundException("The pages does not exist in this context");
        }
        $em = $this->getDoctrine()->getManager();
        if(!$author = $em->getRepository('matuckLibraryBundle:Author')->find($id))
        {
            throw $this->createNotFoundException("The author you requested could not be found");
        }
        $author2 = new Author();
        $form   = $this->createForm(new AuthorType(), $author2);

        $form->bind($this->getRequest());
        if($form->isValid())
        {
            $author->setFacebook($author2->getFacebook());
            $author->setTwitter($author2->getTwitter());
            $author->setBiography($author2->getBiography());
            $em->persist($author);
            $em->flush();
            return $this->redirect($this->generateUrl('matuck_library_browse_author', array('id' => $id)));
        }
        else
        {
            return $this->render('matuckLibraryBundle:Author:bio.html.twig', array(
                'author' => $author,
                'form'   => $form->createView(),
            ));
        }
  }
        
    public function makefavouriteAction($id)
    {
        $iphash = $this->get('matuck_library.iphash')->get();
        $em = $this->getDoctrine()->getManager();
        if($fav = $em->getRepository('matuckLibraryBundle:Authorvotes')->findbyauthorandip($id, $iphash))
        {
            $this->get('session')->getFlashBag()->add('notice', 'You have already made this author one of your favorites.');
            return $this->redirect($this->generateUrl('matuck_library_browse_author', array('id' => $id)));
        }

        $favauthor = new Authorvotes();
        $favauthor->setAuthorid($em->getRepository('matuckLibraryBundle:Author')->find($id));
        $favauthor->setIphash($iphash);
        $em->persist($favauthor);
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('notice', 'You have successfully made this author one of your favourites.');
        return $this->redirect($this->generateUrl('matuck_library_browse_author', array('id' => $id)));
    }
    
    public function removefavouriteAction($id)
    {
        $iphash = $this->get('matuck_library.iphash')->get();
        $em = $this->getDoctrine()->getManager();
        if($fav = $em->getRepository('matuckLibraryBundle:Authorvotes')->findbyauthorandip($id, $iphash))
        {
            $this->get('session')->getFlashBag()->add('notice', 'This author is no longer one of your favorites.');
            $em->remove($fav);
            $em->flush();
        }
        else
        {
            $this->get('session')->getFlashBag()->add('notice', 'You was not one of your favourites');
        }
        return $this->redirect($this->generateUrl('matuck_library_browse_author', array('id' => $id)));
    }
    
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if(!$author = $em->getRepository('matuckLibraryBundle:Author')->find($id))
        {
            throw $this->createNotFoundException("The Author you requested could not be found");
        }
        $indexer = $this->get('matuck_library.searchindexer');
        /* @var $indexer \matuck\LibraryBundle\Lib\Indexer */
        $iphash = $this->get('matuck_library.iphash')->get();
        $isfav = $em->getRepository('matuckLibraryBundle:Authorvotes')->findbyauthorandip($id, $iphash);
        $pager = $em->getRepository('matuckLibraryBundle:book')->findByAuthor($author);
        if($this->getRequest()->get('page'))
        {
            $pager->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $pager->setCurrentPage(1);
        }
        $response = $this->render('matuckLibraryBundle:Author:show.html.twig', array('isfav' => $isfav,'author' => $author, 'pager' => $pager));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    public function editAction($id, $redirect = '')
    {
        $em = $this->getDoctrine()->getManager();
        if(!$author = $em->getRepository('matuckLibraryBundle:Author')->find($id))
        {
            throw $this->createNotFoundException("The author you requested could not be found");
        }
        $em = $this->getDoctrine()->getManager();
        $authorrepo = $em->getRepository('matuckLibraryBundle:Author');
        /* @var $authorrepo \matuck\LibraryBundle\Entity\Authorrepository */
        
        $author = $authorrepo->find($id);
        $form = $this->createForm(new AuthorType, $author);
        $form->remove('createdAt');
        $form->remove('updatedAt');
        return $this->render('matuckLibraryBundle:Author:edit.html.twig', array(
            'author' => $author,
            'form'   => $form->createView(),
            'redirect' => $redirect,
        ));
    }
    
    public function updateAction($id, $redirect = '')
    {
        $em = $this->getDoctrine()->getManager();
        if($this->getRequest()->getMethod() != 'POST')
        {
            throw $this->createNotFoundException("The pages does not exist in this context");
        }
        if(!$author = $em->getRepository('matuckLibraryBundle:Author')->find($id))
        {
            throw $this->createNotFoundException("The author you requested could not be found");
        }
        /* @var $author Author */
        $origauthor = $author;
        $form = $this->createForm(new AuthorType(), $author);
        $form->remove('createdAt');
        $form->remove('updatedAt');
        $form->bind($this->getRequest());
        
        if($form->isValid())
        {
            $author->setUpdatedAt(new \DateTime);
            $em->persist($author);
            $em->flush();
            $indexer = $this->get('matuck_library.searchindexer');
            /* @var $indexer \matuck\LibraryBundle\Lib\Indexer */
            $indexer->deleteAuthor($origauthor);
            $indexer->indexAuthor($author);
            $books = $em->getRepository('matuckLibraryBundle:Book')->findByAuthor($author);
            /* @var $books \Pagerfanta\Pagerfanta */
            $books->setMaxPerPage($books->getNbResults());
            $books->setCurrentPage(1);
            foreach($books->getCurrentPageResults() as $book)
            {
                $indexer->deleteBook($book);
                $indexer->indexBook($book);
            }
            $url = '';
            if($redirect != '')
            {
                $split = explode(':', $redirect);
                if($split[0] == 'bookedit')
                {
                    $url = $this->generateUrl('matuck_library_book_edit', array('id' => $split[1]));
                }
            }
            if($url == '' || $url == NULL)
            {
                $url = $this->generateUrl('matuck_library_author_show', array('id' => $id));
            }
            
            return $this->redirect($url);
        }

        return $this->render('matuckLibraryBundle:Author:edit.html.twig', array(
            'author' => $author,
            'form'   => $form->createView(),
            'redirect' => $redirect,
        ));
    }
    
    public function newAction()
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new AuthorType());
        $form->remove('createdAt');
        $form->remove('updatedAt');
        $response = $this->render('matuckLibraryBundle:Author:new.html.twig', array('form' => $form->createView()));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    public function createAction()
    {
        if ($this->getRequest()->getMethod() != 'POST')
        {
            throw $this->createNotFoundException("The pages does not exist in this context");
        }
        else
        {
            $em = $this->getDoctrine()->getManager();
            $author = new Author();
            $form = $this->createForm(new AuthorType(), $author);
            $form->bindRequest($this->getRequest());

            if($form->isValid())
            {
                $author->setCreatedAt(new \DateTime);
                $author->setUpdatedAt(new \DateTime);
                $em->persist($author);
                $em->flush();
                $indexer = $this->get('matuck_library.searchindexer');
                /* @var $indexer \matuck\LibraryBundle\Lib\Indexer */
                $indexer->indexAuthor($author);
                return $this->redirect($this->generateUrl('matuck_library_author_show', array('id' => $author->getId())));
            }
        }
        return $this->render('matuckLibraryBundle:Author:new.html.twig', array('form' => $form->createView()));
    }
    
    public function mergeAction(Author $author)
    {
        $form = $this->createForm(new mergeAuthorType($author));
        $em = $this->getDoctrine()->getManager();
        $bookrepo = $em->getRepository('matuckLibraryBundle:Book');
        /* @var $bookrepo \matuck\LibraryBundle\Entity\Bookrepository */
        if($this->getRequest()->getMethod() == 'POST')
        {
            $form->bind($this->getRequest());
            $data = $form->getData();
            $to = $data['to'];
            
            $books = $bookrepo->findByAuthor($author);
            foreach($books as $book)
            {
                /* @var $book \matuck\LibraryBundle\Entity\Book */
                $book->setAuthor($to);
                $book->setUpdatedAt(new \DateTime);
                $em->persist($book);
            }
            $em->flush();
            
            $indexer = $this->get('matuck_library.searchindexer');
            /* @var $indexer \matuck\LibraryBundle\Lib\Indexer */
            $indexer->deleteAuthor($author);
            $em->remove($author);
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', sprintf('Authors %s have been merged into author %s', trim($author->getName()), trim($to->getName())));
            return $this->redirect($this->generateUrl('matuck_library_author_show', array('id' => $to->getId())));
        }
        return $this->render('matuckLibraryBundle:Author:merge.html.twig', array(
            'form'   => $form->createView(),
            'author' => $author,
            ));
    }
    
    public function deleteAction(Author $author)
    {
        $indexer = $this->get('matuck_library.searchindexer');
        /* @var $indexer \matuck\LibraryBundle\Lib\Indexer */
        $indexer->deleteAuthor($author);
        $em = $this->getDoctrine()->getEntityManager();
        $books = $em->getRepository('matuckLibraryBundle:Book')->findByAuthor($author);
        /* @var $books \Pagerfanta\Pagerfanta */
        $books->setMaxPerPage($books->getNbResults());
        $books->setCurrentPage(1);
        foreach($books->getCurrentPageResults() as $book)
        {
            $indexer->deleteBook($book);
            $indexer->indexBook($book);
        }
        $em->remove($author);
        $em->flush();
        $this->get('session')->getFlashBag()->add('notice', sprintf('Authors %s has been deleted.', trim($author->getName())));
        return $this->redirect($this->generateUrl('matuck_library_homepage'));
    }
}