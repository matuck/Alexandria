<?php

namespace matuck\LibraryBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use matuck\LibraryBundle\Form\SerieType;
use matuck\LibraryBundle\Entity\Serie;

class SerieController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $series = $em->getRepository('matuckLibraryBundle:Serie')->findAllPaged();
        if($this->getRequest()->get('page'))
        {
            $series->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $series->setCurrentPage(1);
        }
        
        $response = $this->render('matuckLibraryBundle:Serie:index.html.twig', array('pager' => $series));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if(!$serie = $em->getRepository('matuckLibraryBundle:Serie')->find($id))
        {
            throw $this->createNotFoundException("The Series you requested could not be found");
        }
        
        $pager = $em->getRepository('matuckLibraryBundle:book')->findBySerie($serie);
        if($this->getRequest()->get('page'))
        {
            $pager->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $pager->setCurrentPage(1);
        }
        $response = $this->render('matuckLibraryBundle:Serie:show.html.twig', array('serie' => $serie, 'pager' => $pager));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    public function newAction()
    {
        $entity = new Serie();
        $form   = $this->createForm(new SerieType(), $entity);
        $form->remove('createdAt');
        $form->remove('updatedAt');
        $response = $this->render('matuckLibraryBundle:Serie:new.html.twig', array(
            'serie' => $entity,
            'form'   => $form->createView(),
        ));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    public function createAction(Request $request)
    {
        $serie = new Serie();
        $form = $this->createForm(new SerieType(), $serie);
        $form->bind($request);
        if($form->isValid())
        {
            $serie->setCreatedAt(new \DateTime());
            $serie->setUpdatedAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($serie);
            $em->flush();
            $indexer = $this->get('matuck_library.searchindexer');
            /* @var $indexer \matuck\LibraryBundle\Lib\Indexer */
            $indexer->indexSeries($serie);
            return $this->redirect($this->generateUrl('matuck_library_serie_show', array('id' => $serie->getId())));
        }
        else
        {
            $form->remove('createdAt');
            $form->remove('updatedAt');
            return $this->render('matuckLibraryBundle:Serie:new.html.twig', array(
            'serie' => $serie,
            'form'   => $form->createView(),
        ));
        }
    }
    
    public function editAction($id, $redirect = '')
    {
        $em = $this->getDoctrine()->getManager();
        $serie = $em->getRepository('matuckLibraryBundle:Serie')->find($id);

        if (!$serie)
        {
            throw $this->createNotFoundException('Unable to find Serie entity.');
        }

        $editForm = $this->createForm(new SerieType(), $serie);
        $editForm->remove('createdAt');
        $editForm->remove('updatedAt');
        return $this->render('matuckLibraryBundle:Serie:edit.html.twig', array(
            'serie'      => $serie,
            'edit_form'   => $editForm->createView(),
            'redirect' => $redirect,
        ));
    }
    
    public function updateAction(Request $request, $id, $redirect = '')
    {
        
        $em = $this->getDoctrine()->getManager();
        $serie = $em->getRepository('matuckLibraryBundle:Serie')->find($id);

        if (!$serie) {
            throw $this->createNotFoundException('Unable to find Serie entity.');
        }
        /* @var $serie Serie */
        
        $indexer = $this->get('matuck_library.searchindexer');
        /* @var $indexer \matuck\LibraryBundle\Lib\Indexer */
        $indexer->deleteSeries($serie);
        $emptySerie = new Serie();
        $editForm = $this->createForm(new SerieType(), $emptySerie);
        $editForm->bind($request);

        if ($editForm->isValid())
        {
            $serie->setUpdatedAt(new \DateTime());
            $serie->setName($emptySerie->getName());
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
                $url = $this->generateUrl('matuck_library_serie_show', array('id' => $id));
            }
            $em->persist($serie);
            $em->flush();
            $books = $em->getRepository('matuckLibraryBundle:Book')->findBySerie($serie);
            /* @var $books \Pagerfanta\Pagerfanta */
            $books->setMaxPerPage($books->getNbResults());
            $books->setCurrentPage(1);
            foreach($books->getCurrentPageResults() as $book)
            {
                $indexer->deleteBook($book);
                $indexer->indexBook($book);
            }
            $indexer->indexSeries($serie);
            return $this->redirect($url);
        }

        $indexer->indexSeries($serie);
        return $this->render('matuckLibraryBundle:Serie:edit.html.twig', array(
            'serie'      => $serie,
            'edit_form'   => $editForm->createView(),
            'redirect' => $redirect,
        ));
    }
    
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $serie = $em->getRepository('matuckLibraryBundle:Serie')->find($id);
        if (!$serie)
        {
            throw $this->createNotFoundException('Unable to find Serie entity.');
        }
        
        $indexer = $this->get('matuck_library.searchindexer');
        /* @var $indexer \matuck\LibraryBundle\Lib\Indexer */
        
        $books = $em->getRepository('matuckLibraryBundle:Book')->findBySerie($serie);
        /* @var $books \Pagerfanta\Pagerfanta */
        $books->setMaxPerPage($books->getNbResults());
        $books->setCurrentPage(1);
        foreach($books->getCurrentPageResults() as $book)
        {
            $indexer->deleteBook($book);
            $indexer->indexBook($book);
        }
        $indexer->deleteSeries($serie);
        $em->remove($serie);
        $em->flush();
        return $this->redirect($this->generateUrl('matuck_library_serie'));
    }
}