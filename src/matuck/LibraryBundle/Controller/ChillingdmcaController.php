<?php

namespace matuck\LibraryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use matuck\LibraryBundle\Entity\Book;
use matuck\LibraryBundle\Entity\BookRepository;
use matuck\LibraryBundle\Entity\Chillingdmca;
use matuck\LibraryBundle\Form\ChillingdmcaType;

class ChillingdmcaController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $pager = $em->getRepository('matuckLibraryBundle:Chillingdmca')->findAllPaged();
         if($this->getRequest()->get('page'))
        {
            $pager->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $pager->setCurrentPage(1);
        }
        $response = $this->render('matuckLibraryBundle:Chillingdmca:index.html.twig', array('pager' => $pager));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
  
    public function newAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if(!$book = $em->getRepository('matuckLibraryBundle:Book')->find($id))
        {
            throw $this->createNotFoundException("The book you requested could not be found");
        }

        $dmca = new Chillingdmca();
        $dmca->setBookTitle($book->getTitle());
        $dmca->setBookAuthor($book->getAuthor()->getName());
        $form   = $this->createForm(new ChillingdmcaType(), $dmca);
        $form->remove('createdAt');
        $form->remove('updatedAt');
        $form->remove('ipAddress');
        return $this->render('matuckLibraryBundle:Chillingdmca:new.html.twig', array('form' => $form->createView(), 'book' => $book));
    }
  
    public function createAction($bookid)
    {
        if(!$this->getRequest()->getMethod() == 'POST')
        {
            throw $this->createNotFoundException("The pages does not exist in this context");
        }
        $dmca = new Chillingdmca();
        $form   = $this->createForm(new ChillingdmcaType(), $dmca);

        $form->bind($this->getRequest());
        if($form->isValid())
        {
            $dmca->setCreatedAt(new \DateTime());
            $dmca->setUpdatedAt(new \DateTime());
            $dmca->setIpAddress($this->getRequest()->getClientIp());
            $em = $this->getDoctrine()->getManager();
            $bookrepo = $em->getRepository('matuckLibraryBundle:Book');
            /* @var $bookrepo BookRepository */
            $book = $bookrepo->find($bookid);
            /* @var $book Book */
            $book->setIsPublic(FALSE);
            $em->persist($dmca);
            $em->persist($book);
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', sprintf('DMCA Take down has been registered for %s', $dmca->getBookTitle()));
            return $this->redirect($this->generateUrl('matuck_library_homepage'));
        }
        else
        {
            $this->get('session')->getFlashBag()->add('error', 'Failed to create the dmca take down please try again.');
            return $this->redirect($this->generateUrl('matuck_library_chillingdmca_new', array('id' => $bookid)));
        }
    }
    
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if(!$dmca = $em->getRepository('matuckLibraryBundle:Chillingdmca')->find($id))
        {
            throw $this->createNotFoundException("The dmca request you requested could not be found");
        }
        $form = $this->createForm(new ChillingdmcaType(), $dmca);
        $form->remove('createdAt');
        $form->remove('updatedAt');
        $form->remove('ipAddress');
        return $this->render('matuckLibraryBundle:Chillingdmca:edit.html.twig', array('id' => $id, 'form' => $form->createView()));
    }
    
    public function updateAction($id)
    {
        if(!$this->getRequest()->getMethod() == 'POST')
        {
            throw $this->createNotFoundException("The pages does not exist in this context");
        }
        $em = $this->getDoctrine()->getManager();
        if(!$dmca = $em->getRepository('matuckLibraryBundle:Chillingdmca')->find($id))
        {
            throw $this->createNotFoundException("The dmca request you requested could not be found");
        }
        /* @var $dmca Chillingdmca */
        $dmca2 = new Chillingdmca();
        $form = $this->createForm(new ChillingdmcaType(), $dmca2);
        $form->bind($this->getRequest());
        if($form->isValid())
        {
            $dmca->setUpdatedAt(new \DateTime);
            $dmca->setBookAuthor($dmca2->getBookAuthor());
            $dmca->setBookTitle($dmca2->getBookTitle());
            $dmca->setDmcaName($dmca2->getDmcaName());
            $dmca->setDmcaEmail($dmca2->getDmcaEmail());
            $em->persist($dmca);
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', sprintf('DMCA Take down has been updated for %s', $dmca->getBookTitle()));
            return $this->redirect($this->generateUrl('matuck_library_chillingdmca'));
        }
        else
        {
            $this->get('session')->getFlashBag()->add('error', sprintf('Unable to edit the DMCA Takedown for %s. Please try again.', $dmca->getBookTitle()));
            return $this->redirect($this->generateUrl('matuck_library_chillingdmca_edit', array('id' => $id)));
        }
    }

    public function delete($id)
    {
        $em = $this->getDoctrine()->getManager();
        if(!$dmca = $em->getRepository('matuckLibraryBundle:Chillingdmca')->find($id))
        {
            throw $this->createNotFoundException("The dmca request you requested could not be found");
        }
        $this->get('session')->getFlashBag()->add('notice', sprintf('DMCA Take down has been updated for %s', $dmca->getBookTitle()));
        $em->remove($dmca);
        $em->flush();
        return $this->redirect($this->generateUrl('matuck_library_chillingdmca'));
    }
}