<?php

namespace matuck\LibraryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use matuck\LibraryBundle\Entity\Featured;
use matuck\LibraryBundle\Form\FeaturedType;

class FeaturedController extends Controller
{
    /**
     * @Route("/featured", name="matuck_library_featured")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $pager = $em->getRepository('matuckLibraryBundle:Featured')->PagerOrderbyUpdated();
        /* @var $pager \Pagerfanta\Pagerfanta */
        $pager->getMaxPerPage(10);
        if($this->getRequest()->get('page'))
        {
            $pager->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $pager->setCurrentPage(1);
        }
        $response = $this->render('matuckLibraryBundle:Featured:index.html.twig', array('pager' => $pager));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    /**
     * @Route("/admin/featured/make/{book}", name="matuck_library_featured_make")
     */
    public function makeFeatured($book)
    {
        $em = $this->getDoctrine()->getManager();
        if(($book = $em->getRepository('matuckLibraryBundle:Book')->find($book))!= NULL)
        {
            $featured = new Featured();
            $featured->setBook($book);
            $featured->setCreatedAt(new \DateTime);
            $featured->setUpdatedAt(new \DateTime);
            $form   = $this->createForm(new FeaturedType(), $featured);
            $form->remove('book');
            $form->remove('createdAt');
            $form->remove('updatedAt');
            if($this->getRequest()->getMethod() == 'POST')
            {
                
                $form->bind($this->getRequest());
                if($form->isValid())
                {
                    $em->persist($featured);
                    $em->flush();
                    return $this->redirect($this->generateUrl('matuck_library_book_show', array('id' => $book->getId())));
                }
            }
            return $this->render('matuckLibraryBundle:Featured:make.html.twig', array(
                'book' => $book,
                'form'   => $form->createView(),
            ));
        }
        else
        {
            throw $this->createNotFoundException('The Book could not be found to make it a featured book');
        }
    }
}