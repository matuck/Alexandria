<?php

namespace matuck\LibraryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use matuck\LibraryBundle\Entity\Tag;
use matuck\LibraryBundle\Form\TagType;
use matuck\LibraryBundle\Form\newTagType;
use matuck\LibraryBundle\Form\mergeTagType;

class TagController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $tags = $em->getRepository('matuckLibraryBundle:Tag')->findAllPagerOrderbyName();
        /* @var $tags \Pagerfanta\Pagerfanta */
        $tags->setMaxPerPage(10);
        if($this->getRequest()->get('page'))
        {
            $tags->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $tags->setCurrentPage(1);
        }
        return $this->render('matuckLibraryBundle:Tag:index.html.twig', array('tags' => $tags));
    }
    
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $tagrepo = $em->getRepository('matuckLibraryBundle:Tag');
        /* @var $tagrepo \matuck\LibraryBundle\Entity\Tagrepository */
        $taggingrepo = $em->getRepository('matuckLibraryBundle:Tagging');
        /* @var $taggpingrepo \matuck\LibraryBundle\Entity\Taggingrepository */
        $bookrepo = $em->getRepository('matuckLibraryBundle:Book');
       /* @var $bookrepo \matuck\LibraryBundle\Entity\Bookrepository */
        $booktag = $tagrepo->find($id);
		
        $resources = $taggingrepo->findResourcesByTypeandTag('book', $booktag);
        /* @var $resources \Pagerfanta\Pagerfanta */
        $resources->setMaxPerPage(10);
        if($this->getRequest()->get('page'))
        {
            $resources->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $resources->setCurrentPage(1);
        }

        //build array of resources ids
        $resourceids = array();
        
        foreach($resources->getCurrentPageResults() as $resource)
        {
            /* @var \matuck\LibraryBundle\Entity\Tagging */
            $resourceids[] = $resource->getResourceId();
        }
        if(empty($resourceids))
        {
            $resourceids = array('none');
        }
        $books = $bookrepo->findBooksinArrayofIDs($resourceids);
        return $this->render('matuckLibraryBundle:Tag:show.html.twig', array(
            'tag' => $booktag,
            'books' => $books,
            'pager' => $resources,
        ));
    }

    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $tagrepo = $em->getRepository('matuckLibraryBundle:Tag');
        /* @var $tagrepo \matuck\LibraryBundle\Entity\Tagrepository */

        $tag = $tagrepo->find($id);
        $form   = $this->createForm(new TagType(), $tag);
        $form->remove('createdAt');
        $form->remove('updatedAt');
        $form->remove('slug');
        return $this->render('matuckLibraryBundle:Tag:edit.html.twig', array(
            'tag' => $tag,
            'form'   => $form->createView(),
        ));
    }

    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $taggingrepo = $em->getRepository('matuckLibraryBundle:Tagging');
        /* @var $taggpingrepo \matuck\LibraryBundle\Entity\Taggingrepository */
        $ids = $taggingrepo->findBy(array('tag' => $id));
        $tag = $em->getRepository('matuckLibraryBundle:Tag')->find($id);
        
        foreach($ids as $entity)
        {
            $em->remove($entity);
        }
        $em->remove($tag);
        $em->flush();
        return $this->redirect($this->generateUrl('matuck_library_tags'));
    }

    public function newAction()
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new newTagType());

        return $this->render('matuckLibraryBundle:Tag:new.html.twig', array('form' => $form->createView()));
    }

    public function createAction()
    {
        if ($this->getRequest()->getMethod() != 'POST')
        {
            throw $this->createNotFoundException("The pages does not exist in this context");
        }
        else
        {
            $tagManager = $this->get('fpn_tag.tag_manager');
            /* @var $tagManager \FPN\TagBundle\Entity\TagManager */
            $form = $this->createForm(new newTagType());
            $form->bindRequest($this->getRequest());

            // data is an array with "name"
            $data = $form->getData();
            if($data['name'] != '' && $data['name'] != NULL)
            {
                $tag = $tagManager->loadOrCreateTag($data['name']);
                return $this->redirect($this->generateUrl('matuck_library_tags_show', array('id' => $tag->getId())));
                
            }
        }
        return $this->render('matuckLibraryBundle:Tag:new.html.twig', array('form' => $form->createView()));
    }

    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        if($this->getRequest()->getMethod() != 'POST')
        {
            throw $this->createNotFoundException("The pages does not exist in this context");
        }
        if(!$tag = $em->getRepository('matuckLibraryBundle:Tag')->find($id))
        {
            throw $this->createNotFoundException("The tag you requested could not be found");
        }
        /* @var $tag Tag */
        $form = $this->createForm(new TagType(), $tag);
        $form->remove('createdAt');
        $form->remove('updatedAt');
        $form->remove('slug');
        $form->bind($this->getRequest());
                
        if($form->isValid())
        {
            $tag->setUpdatedAt(new \DateTime);
            $em->persist($tag);
            $em->flush();
            
            return $this->redirect($this->generateUrl('matuck_library_book_show', array('id' => $id)));
        }

        return $this->render('matuckLibraryBundle:Tag:edit.html.twig', array(
            'tag' => $tag,
            'form'   => $form->createView(),
        ));
    }
    
    public function mergeAction()
    {
        $form = $this->createForm(new mergeTagType());
        $em = $this->getDoctrine()->getManager();
        $taggingrepo = $em->getRepository('matuckLibraryBundle:Tagging');
        /* @var $taggingrepo \matuck\LibraryBundle\Entity\Taggingrepository */
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
            $totaggings = $taggingrepo->findResourcesByTag($to);
            //convert to taggings to an array with resource type tag id and resourceid
            $totaggings2 = array();
            foreach($totaggings as $key => $totagging)
            {
                $totaggings2[$key]['resourceid'] = $totagging->getResourceId();
                $totaggings2[$key]['resourcetype'] = $totagging->getResourceType();
                $totaggings2[$key]['tagid'] = $totagging->getTag()->getId();
            }
            $totaggings = $totaggings2;
            unset($totaggings2);
            $message['to'] = trim($to->getName());
            $message['from'] = array();
            foreach($from as $tag)
            {
                $message['from'][] = trim($tag->getName());
                $taggings = $taggingrepo->findResourcesByTag($tag);
                foreach($taggings as $tagging)
                {
                    $testcase = array('resourceid' => $tagging->getResourceId(), 'resourcetype' => $tagging->getResourceType(), 'tagid' => $to->getId());
                    if(in_array($testcase, $totaggings))
                    {
                        $em->remove($tagging);
                    }
                    else
                    {
                        $tagging->setTag($to);
                        $em->persist($tagging);
                    }
                }
                $em->remove($tag);
            }
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', sprintf('Tags %s have been merged into tag %s', implode(', ', $message['from']), $message['to']));
        }
        //rebuild form so it gets rid of things that have been deleted.
        $form = $this->createForm(new mergeTagType());
        return $this->render('matuckLibraryBundle:Tag:merge.html.twig', array(
            'form'   => $form->createView(),
            ));
    }
}
