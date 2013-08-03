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
        $form->remove('name');
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
    
    public function executeRemovefavourite(sfWebRequest $request)
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
    
    public function editAction($id)
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
        ));
    }
    
    public function updateAction($id)
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
        $form = $this->createForm(new AuthorType(), $author);
        $form->remove('createdAt');
        $form->remove('updatedAt');
        $form->bind($this->getRequest());
        $author = $author->getName();
        if($form->isValid())
        {
            $author->setUpdatedAt(new \DateTime);
            $em->persist($author);
            $em->flush();
            $index = $this->get('ivory_lucene_search')->getIndex('master');
            /* @var $index \Zend\Search\Lucene\Index */
            $results = $index->find('type:author AND name:"'.$author.'"');
            foreach($results as $doc)
            {
                /* @var $doc Document */
                if($author->getId() == $doc->objid && $doc->type == 'author')
                {
                    $index->delete($doc->id);
                    $index->commit();
                }
            }
            $doc = new Document();
            $doc->addField(Field::keyword('type', 'author'));
            $doc->addField(Field::binary('objid', $author->getId()));
            $doc->addField(Field::text('name', $author->getName()));
            $doc->addField(Field::text('bio', $author->getBiography()));
            $index->addDocument($doc);
            $index->commit();
            return $this->redirect($this->generateUrl('matuck_library_author_show', array('id' => $id)));
        }

        return $this->render('matuckLibraryBundle:Author:edit.html.twig', array(
            'author' => $author,
            'form'   => $form->createView(),
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
                $index = $this->get('ivory_lucene_search')->getIndex('master');
                /* @var $index \Zend\Search\Lucene\Index */
                $doc = new Document();
                $doc->addField(Field::keyword('type', 'author'));
                $doc->addField(Field::binary('objid', $author->getId()));
                $doc->addField(Field::text('name', $author->getName()));
                $doc->addField(Field::text('bio', $author->getBiography()));
                $index->addDocument($doc);
                $index->commit();
                return $this->redirect($this->generateUrl('matuck_library_author_show', array('id' => $author->getId())));
            }
        }
        return $this->render('matuckLibraryBundle:Author:new.html.twig', array('form' => $form->createView()));
    }
    
    /* @todo split the update part of this action */
    public function mergeAction()
    {
        $form = $this->createForm(new mergeAuthorType());
        $em = $this->getDoctrine()->getManager();
        $bookrepo = $em->getRepository('matuckLibraryBundle:Book');
        /* @var $bookrepo \matuck\LibraryBundle\Entity\Bookrepository */
        if($this->getRequest()->getMethod() == 'POST')
        {
            $form->bind($this->getRequest());
            $data = $form->getData();
            $from = $data['from']->toArray();
            $to = $data['to'];
            $key = array_search($to, $from);
            if($key !== FALSE)
            {
                unset($from[$key]);
            }
            $message['to'] = trim($to->getName());
            $message['from'] = array();
            foreach($from as $author)
            {
                $message['from'][] = trim($author->getName());
                $books = $bookrepo->findByAuthor($author);
                foreach($books as $book)
                {
                    /* @var $book \matuck\LibraryBundle\Entity\Book */
                    $book->setAuthor($to);
                    $book->setUpdatedAt(new \DateTime);
                    $em->persist($book);
                }
                $em->flush();
                $authorname = $author->getName();
                $em->remove($author);
                $index = $this->get('ivory_lucene_search')->getIndex('master');
                /* @var $index \Zend\Search\Lucene\Index */
                $results = $index->find('type:author AND author:"'.$authorname.'"');
                foreach($results as $doc)
                {
                    /* @var $doc Document */
                    if($author->getId() == $doc->objid && $doc->type == 'author')
                    {
                        $index->delete($doc->id);
                        $index->commit();
                    }
                }
            }
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', sprintf('Authors %s have been merged into author %s', implode(', ', $message['from']), $message['to']));
        }
        //rebuild form so it gets rid of things that have been deleted.
        $form = $this->createForm(new mergeAuthorType());
        return $this->render('matuckLibraryBundle:Author:merge.html.twig', array(
            'form'   => $form->createView(),
            ));
    }
}