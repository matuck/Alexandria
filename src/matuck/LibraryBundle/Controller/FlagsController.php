<?php

namespace matuck\LibraryBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;
use matuck\LibraryBundle\Entity\Flags;
use matuck\LibraryBundle\Form\FlagsType;

/**
 * Flags controller.
 *
 */
class FlagsController extends Controller
{
    /**
     * Lists all Flags entities.
     *
     */
    public function indexAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $filters = array();
        //stuff to do if the filter form was submitted.
        if($request->getMethod() == 'POST')
        {
            $formdata = $request->get('form');
            //reset all filters
            if($formdata['do'] == 'Reset Filters')
            {
                $session->remove('flagfilters');
                return $this->redirect($this->generateUrl('matuck_library_flags'));
            }
            else //set filter values and cookie
            {
                $filters = $formdata;
                unset($filters['_token']);
                unset($filters['do']);
                $session->set('flagfilters', serialize($filters));
            }
        }
        if(empty($filters))
        {
            //load filters from cookie
            if(!$filters = unserialize($session->get('flagfilters')))
            {
                //load default values since cookie did not load
                $filters['open'] = 1;
                $filters['close'] = 1;
                $filters['file'] = 1;
                $filters['cover'] = 1;
                $filters['metadata'] = 1;
                $filters['copyright'] = 1;
                $filters['other'] = 1;
                $filters['startdate'] = '';
                $filters['enddate'] = '';
            }
        }
        //setup form
        $filters_form = $this->createFormBuilder();
        if($filters['open'])
        {
            $filters_form->add('open', 'checkbox', array('attr' => array('checked' => 'checked'), 'label' => 'Open', 'required' => FALSE));
        }
        else
        {
            $filters_form->add('open', 'checkbox', array('label' => 'Open', 'required' => FALSE));
        }
        if($filters['close'])
        {
            $filters_form->add('close', 'checkbox', array('attr' => array('checked' => 'checked'), 'label' => 'Close', 'required' => FALSE));
        }
        else
        {
            $filters_form->add('close', 'checkbox', array('label' => 'Close', 'required' => FALSE));
        }
        if($filters['file'])
        {
            $filters_form->add('file', 'checkbox', array('attr' => array('checked' => 'checked'), 'label' => 'File', 'required' => FALSE));
        }
        else
        {
            $filters_form->add('file', 'checkbox', array('label' => 'File', 'required' => FALSE));
        }
        if($filters['cover'])
        {
            $filters_form->add('cover', 'checkbox', array('attr' => array('checked' => 'checked'), 'label' => 'Cover', 'required' => FALSE));
        }
        else
        {
            $filters_form->add('cover', 'checkbox', array('label' => 'Cover', 'required' => FALSE));
        }
        if($filters['metadata'])
        {
            $filters_form->add('metadata', 'checkbox', array('attr' => array('checked' => 'checked'), 'label' => 'Metadata', 'required' => FALSE));
        }
        else
        {
            $filters_form->add('metadata', 'checkbox', array('label' => 'Metadata', 'required' => FALSE));
        }
        if($filters['copyright'])
        {
            $filters_form->add('copyright', 'checkbox', array('attr' => array('checked' => 'checked'), 'label' => 'Copyright', 'required' => FALSE));
        }
        else
        {
            $filters_form->add('copyright', 'checkbox', array('label' => 'Copyright', 'required' => FALSE));
        }
        if($filters['other'])
        {
            $filters_form->add('other', 'checkbox', array('attr' => array('checked' => 'checked'), 'label' => 'Other', 'required' => FALSE));
        }
        else
        {
            $filters_form->add('other', 'checkbox', array('label' => 'Other', 'required' => FALSE));
        }
        if($filters['startdate'] != '' && $filters['startdate'] != NULL)
        {
                $filters_form->add('startdate', 'text', array('data' => $filters['startdate'], 'required' => FALSE));
        }
        else
        {
            $filters_form->add('startdate', 'text', array('required' => FALSE));
        }
        if($filters['enddate'] != '' && $filters['enddate'] != NULL)
        {
            $filters_form->add('enddate', 'text', array('data' => $filters['enddate'], 'required' => FALSE));
        }
        else
        {
            $filters_form->add('enddate', 'text', array('required' => FALSE));
        }
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('matuckLibraryBundle:Flags')->findFilteredPaged($filters);
        if($this->getRequest()->get('page'))
        {
            $entities->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $entities->setCurrentPage(1);
        }
        $response = $this->render('matuckLibraryBundle:Flags:index.html.twig', array(
            'entities' => $entities,
            'filters_form' => $filters_form->getForm()->createView(),
        ));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }

    /**
     * Creates a new Flags entity.
     *
     */
    public function createAction(Request $request, $id)
    {
        $entity  = new Flags();
        $form = $this->createForm(new FlagsType(), $entity);
        if($this->container->getParameter('matuck_library_usecaptchas'))
        {
            $form->add('captcha', 'captcha');
        }
        $form->bind($request);

        if ($form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $entity->setCreatedAt(new \DateTime());
            $entity->setUpdatedAt(new \DateTime());
            $entity->setComplete(false);
            $entity->setBook($em->getRepository('matuckLibraryBundle:Book')->find($id));
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('matuck_library_book_show', array('id' => $id)));
        }

        return $this->render('matuckLibraryBundle:Flags:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'bookid' => $id
        ));
    }

    /**
     * Displays a form to create a new Flags entity.
     *
     */
    public function newAction($id)
    {
        $entity = new Flags();
        $form   = $this->createForm(new FlagsType(), $entity);
        $form->remove('book');
        $form->remove('createdAt');
        $form->remove('updatedAt');
        $form->remove('complete');
        if($this->container->getParameter('matuck_library_usecaptchas'))
        {
            $form->add('captcha', 'captcha');
        }
        return $this->render('matuckLibraryBundle:Flags:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'bookid' => $id
        ));
    }

    /**
     * Finds and displays a Flags entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('matuckLibraryBundle:Flags')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Flags entity.');
        }

        //$deleteForm = $this->createDeleteForm($id);

        $response = $this->render('matuckLibraryBundle:Flags:show.html.twig', array(
            'entity'      => $entity,
          /*  'delete_form' => $deleteForm->createView(),  */      ));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }

    /**
     * Displays a form to edit an existing Flags entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('matuckLibraryBundle:Flags')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Flags entity.');
        }

        $editForm = $this->createForm(new FlagsType(), $entity);
        $editForm->remove('book');
        $editForm->remove('createdAt');
        $editForm->remove('updatedAt');
        if($this->container->getParameter('matuck_library_usecaptchas'))
        {
            $editForm->add('captcha', 'captcha');
        }
        return $this->render('matuckLibraryBundle:Flags:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        ));
    }

    /**
     * Edits an existing Flags entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('matuckLibraryBundle:Flags')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Flags entity.');
        }
        /* @var $entity Flags */
        $emptyflag = new Flags();
        $editForm = $this->createForm(new FlagsType(), $emptyflag);
        if($this->container->getParameter('matuck_library_usecaptchas'))
        {
            $editForm->add('captcha', 'captcha');
        }
        $editForm->bind($request);

        if ($editForm->isValid())
        {
            $entity->setComplete($emptyflag->getComplete());
            $entity->setType($emptyflag->getType());
            $entity->setTitle($emptyflag->getTitle());
            $entity->setUpdatedAt(new \DateTime());
            $url = $this->generateUrl('matuck_library_book_show', array('id' => $entity->getBook()->getId()));
            $em->persist($entity);
            $em->flush();

            return $this->redirect($url);
        }

        return $this->render('matuckLibraryBundle:Flags:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        ));
    }

    /**
     * Deletes a Flags entity.
     *
     */
    public function deleteAction(Request $request, $id, $redirect)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('matuckLibraryBundle:Flags')->find($id);
        if (!$entity)
        {
            throw $this->createNotFoundException('Unable to find Flags entity.');
        }

        switch($redirect)
        {
            case 'book':
                $url = $this->generateUrl('matuck_library_book_show', array('id' => $entity->getBook()->getId()));
                break;
            case 'flags':
                $url = $this->generateUrl('matuck_library_flags');
                break;
            default:
                throw $this->createNotFoundException();
                break;
        }
        
        $em->remove($entity);
        $em->flush();
        return $this->redirect($url);
    }
    
    public function closeAction($id, $redirect)
    {
        $em = $this->getDoctrine()->getManager();
        if($flag = $em->getRepository('matuckLibraryBundle:Flags')->find($id))
        {
            if($flag->getComplete())
            {
              $flag->setComplete(false);
            }
            else
            {
              $flag->setComplete(true);
            }
            $em->persist($flag);
            $em->flush();
            switch ($redirect)
            {
              case 'book':
                return $this->redirect($this->generateUrl('matuck_library_book_show', array('id' => $flag->getBook()->getId())));
                break;
              case 'flags':
                return $this->redirect($this->generateUrl('matuck_library_flags'));
                break;
              default:
                return $this->redirect($this->generateUrl('matuck_library_flags_show', array('id' => $flag->getId())));
                break;
            }
        }
        else
        {
            throw $this->createNotFoundException('Flag was not found!');
        }
    }
}
