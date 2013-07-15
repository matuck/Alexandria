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
        
        return $this->render('matuckLibraryBundle:Serie:index.html.twig', array('pager' => $series));
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
        return $this->render('matuckLibraryBundle:Serie:show.html.twig', array('serie' => $serie, 'pager' => $pager));
    }
    
    public function newAction()
    {
        $entity = new Serie();
        $form   = $this->createForm(new SerieType(), $entity);
        $form->remove('createdAt');
        $form->remove('updatedAt');
        return $this->render('matuckLibraryBundle:Serie:new.html.twig', array(
            'serie' => $entity,
            'form'   => $form->createView(),
        ));
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
            $index = $this->get('ivory_lucene_search')->getIndex('master');
            /* @var $index \Zend\Search\Lucene\Index */
            $doc = new Document();
            $doc->addField(Field::keyword('type', 'serie'));
            $doc->addField(Field::binary('objid', $serie->getId()));
            $doc->addField(Field::text('name', $serie->getName()));
            $index->addDocument($doc);
            $index->commit();
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
    
    public function editAction($id)
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
        ));
    }
    
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $serie = $em->getRepository('matuckLibraryBundle:Serie')->find($id);

        if (!$serie) {
            throw $this->createNotFoundException('Unable to find Serie entity.');
        }
        /* @var $serie Serie */
        $emptySerie = new Serie();
        $editForm = $this->createForm(new SerieType(), $emptySerie);
        $editForm->bind($request);

        if ($editForm->isValid())
        {
            $serie->setUpdatedAt(new \DateTime());
            $serie->setName($emptySerie->getName());
            $url = $this->generateUrl('matuck_library_serie_show', array('id' => $id));
            $em->persist($serie);
            $em->flush();
            $index = $this->get('ivory_lucene_search')->getIndex('master');
            /* @var $index \Zend\Search\Lucene\Index */
            $results = $index->find('type:serie AND name:"'.$serie.getName().'"');
            foreach($results as $doc)
            {
                /* @var $doc Document */
                if($serie->getId() == $doc->objid && $doc->type == 'serie')
                {
                    $index->delete($doc->id);
                    $index->commit();
                }
            }
            $doc = new Document();
            $doc->addField(Field::keyword('type', 'serie'));
            $doc->addField(Field::binary('objid', $serie->getId()));
            $doc->addField(Field::text('name', $serie->getName()));
            $index->addDocument($doc);
            $index->commit();
            return $this->redirect($url);
        }

        return $this->render('matuckLibraryBundle:Serie:edit.html.twig', array(
            'serie'      => $serie,
            'edit_form'   => $editForm->createView(),
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
        
        $index = $this->get('ivory_lucene_search')->getIndex('master');
        /* @var $index \Zend\Search\Lucene\Index */
        $results = $index->find('type:serie AND name:"'.$serie->getName().'"');
        foreach($results as $doc)
        {
            /* @var $doc Document */
            if($book->getId() == $doc->objid && $doc->type == 'serie')
            {
                $index->delete($doc->id);
                $index->commit();
            }
        }
        $em->remove($serie);
        $em->flush();
        return $this->redirect($this->generateUrl('matuck_library_serie'));
    }
}