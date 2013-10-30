<?php

namespace matuck\LibraryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Pagerfanta\Pagerfanta;
use matuck\LibraryBundle\Entity\Book;
use matuck\LibraryBundle\Entity\Author;
use matuck\LibraryBundle\Entity\Serie;
use matuck\LibraryBundle\Entity\Tag;

class CatalogController extends Controller
{    
    public function indexAction()
    {
        $title = $this->container->getParameter('matuck_library_sitetitle');
        $author = $this->container->getParameter('matuck_library_catalogauthor');
        $email = $this->container->getParameter('matuck_library_catalogemail');
        $em = $this->getDoctrine()->getEntityManager();
        
        $latestBookDate = $em->getRepository('matuckLibraryBundle:Book')->getlastbookdate();

        $latestAuthorDate = $em->getRepository('matuckLibraryBundle:Author')->getlastauthordate();

        $latestSeriesDate = $em->getRepository('matuckLibraryBundle:Serie')->getlastseriedate();
        $response = $this->render('matuckLibraryBundle:Catalog:index.atom.twig', array(
            'title' => $title,
            'author' => $author,
            'email' => $email,
            'latestBookDate' => $latestBookDate,
            'latestAuthorDate' => $latestAuthorDate,
            'latestSeriesDate' => $latestSeriesDate,
        ));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    public function opensearchAction()
    {
        return $this->render('matuckLibraryBundle:Catalog:opensearch.atom.twig');
    }
    
    public function newAction()
    {
        $title = $this->container->getParameter('matuck_library_sitetitle');
        $author = $this->container->getParameter('matuck_library_catalogauthor');
        $email = $this->container->getParameter('matuck_library_catalogemail');
        
        $em = $this->getDoctrine()->getManager();
        $books = $em->getRepository('matuckLibraryBundle:Book')->newestbooks();
        if($this->getRequest()->get('page'))
        {
            $books->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $books->setCurrentPage(1);
        }
        /* @var $books \Pagerfanta\Pagerfanta */
        
        $response = $this->render('matuckLibraryBundle:Catalog:new.atom.twig', array(
            'title' => $title,
            'author' => $author,
            'email' => $email,
            'books' => $books,
        ));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    public function popularAction()
    {
        $title = $this->container->getParameter('matuck_library_sitetitle');
        $author = $this->container->getParameter('matuck_library_catalogauthor');
        $email = $this->container->getParameter('matuck_library_catalogemail');
        
        $em = $this->getDoctrine()->getManager();
        $books = $em->getRepository('matuckLibraryBundle:Book')->popularbooks();
        if($this->getRequest()->get('page'))
        {
            $books->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $books->setCurrentPage(1);
        }
        /* @var $books \Pagerfanta\Pagerfanta */
        
        $response = $this->render('matuckLibraryBundle:Catalog:popular.atom.twig', array(
            'title' => $title,
            'author' => $author,
            'email' => $email,
            'books' => $books,
        ));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    public function abcAction($entity, $letter = '')
    {
        $title = $this->container->getParameter('matuck_library_sitetitle');
        $author = $this->container->getParameter('matuck_library_catalogauthor');
        $email = $this->container->getParameter('matuck_library_catalogemail');
        $templates = array('book', 'author', 'series', 'tags');
        if(!in_array($entity, $templates))
        {
            throw $this->createNotFoundException();
        }
        if($letter != '')
        {
            $response = $this->render('matuckLibraryBundle:Catalog:abc'.$entity.'_sub.atom.twig', array(
                'title' => $title,
                'author' => $author,
                'email' => $email,
                'letter' => $letter
            ));
        }
        else
        {
            $response = $this->render('matuckLibraryBundle:Catalog:abc'.$entity.'.atom.twig', array(
                'title' => $title,
                'author' => $author,
                'email' => $email,
            ));
        }
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    public function titlesletterAction($letter)
    {
        $title = $this->container->getParameter('matuck_library_sitetitle');
        $author = $this->container->getParameter('matuck_library_catalogauthor');
        $email = $this->container->getParameter('matuck_library_catalogemail');
        
        $em = $this->getDoctrine()->getManager();
        $bookrepo = $em->getRepository('matuckLibraryBundle:Book');
        /* @var $bookrepo \matuck\LibraryBundle\Entity\Bookrepository */
        switch($letter)
        {
            case 'all':
                $books = $bookrepo->findAllPagerOrderbyTitle();
                break;
            case 'number':
                $books = $bookrepo->findByBookswithNumber();
                break;
            default:
                $books = $bookrepo->findByFirstLetterPaged($letter);
                break;
        }
        /* @var $books Pagerfanta */
        $books->setMaxPerPage(10);
        if($this->getRequest()->get('page'))
        {
            $books->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $books->setCurrentPage(1);
        }
        $response = $this->render('matuckLibraryBundle:Catalog:titlesbooks.atom.twig', array(
            'title' => $title,
            'author' => $author,
            'email' => $email,
            'books' => $books,
            'letter' => $letter,
        ));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    public function authorletterAction($letter)
    {
        $title = $this->container->getParameter('matuck_library_sitetitle');
        $author = $this->container->getParameter('matuck_library_catalogauthor');
        $email = $this->container->getParameter('matuck_library_catalogemail');
        
        $em = $this->getDoctrine()->getManager();
        $authorrepo = $em->getRepository('matuckLibraryBundle:Author');
        /* @var $authorrepo \matuck\LibraryBundle\Entity\Authorrepository */
        if ( $letter == 'all')
        {
            $authors = $authorrepo->findAllPagerOrderbyName();
        }
        else
        {
            $authors = $authorrepo->findByFirstLetterPaged($letter);
        }
        /* @var $books Pagerfanta */
        $authors->setMaxPerPage(10);
        if($this->getRequest()->get('page'))
        {
            $authors->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $authors->setCurrentPage(1);
        }
        $response = $this->render('matuckLibraryBundle:Catalog:authorslist.atom.twig', array(
            'title' => $title,
            'author' => $author,
            'email' => $email,
            'authors' => $authors,
            'letter' => $letter,
        ));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    public function authorbooksAction($id)
    {
        $title = $this->container->getParameter('matuck_library_sitetitle');
        $author = $this->container->getParameter('matuck_library_catalogauthor');
        $email = $this->container->getParameter('matuck_library_catalogemail');
        
        $em = $this->getDoctrine()->getManager();
        $authorrepo = $em->getRepository('matuckLibraryBundle:Author');
        $bookrepo = $em->getRepository('matuckLibraryBundle:Book');
        /* @var $authorrepo \matuck\LibraryBundle\Entity\Authorrepository */
        /* @var $bookrepo \matuck\LibraryBundle\Entity\Bookrepository */
        $bookauthor = $authorrepo->find($id);
        $books = $bookrepo->findByAuthor($bookauthor);
        /* @var $books Pagerfanta */
        $books->setMaxPerPage(10);
        if($this->getRequest()->get('page'))
        {
            $books->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $books->setCurrentPage(1);
        }
        $response = $this->render('matuckLibraryBundle:Catalog:authorbooks.atom.twig', array(
            'title' => $title,
            'author' => $author,
            'email' => $email,
            'authored' => $bookauthor,
            'books' => $books,
        ));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    public function seriesletterAction($letter)
    {
        $title = $this->container->getParameter('matuck_library_sitetitle');
        $author = $this->container->getParameter('matuck_library_catalogauthor');
        $email = $this->container->getParameter('matuck_library_catalogemail');
        
        $em = $this->getDoctrine()->getManager();
        $seriesrepo = $em->getRepository('matuckLibraryBundle:Serie');
        /* @var $seriesrepo \matuck\LibraryBundle\Entity\Serierepository */
        switch($letter)
        {
            case 'all':
                $series = $seriesrepo->findAllPagerOrderbyName();
                break;
            case 'number':
                $series = $seriesrepo->findByBookswithNumber();
                break;
            default:
                $series = $seriesrepo->findByFirstLetterPaged($letter);
                break;
        }
        /* @var $series Pagerfanta */
        $series->setMaxPerPage(10);
        if($this->getRequest()->get('page'))
        {
            $series->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $series->setCurrentPage(1);
        }
        $response = $this->render('matuckLibraryBundle:Catalog:serieslist.atom.twig', array(
            'title' => $title,
            'author' => $author,
            'email' => $email,
            'series' => $series,
            'letter' => $letter,
        ));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    public function seriesbooksAction($id)
    {
        $title = $this->container->getParameter('matuck_library_sitetitle');
        $author = $this->container->getParameter('matuck_library_catalogauthor');
        $email = $this->container->getParameter('matuck_library_catalogemail');
        
        $em = $this->getDoctrine()->getManager();
        $seriesrepo = $em->getRepository('matuckLibraryBundle:Serie');
        $bookrepo = $em->getRepository('matuckLibraryBundle:Book');
        /* @var $seriesrepo \matuck\LibraryBundle\Entity\Authorrepository */
        /* @var $bookrepo \matuck\LibraryBundle\Entity\Bookrepository */
        $bookseries = $seriesrepo->find($id);
        $books = $bookrepo->findByAuthor($bookseries);
        /* @var $books Pagerfanta */
        $books->setMaxPerPage(10);
        if($this->getRequest()->get('page'))
        {
            $books->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $books->setCurrentPage(1);
        }
        $response = $this->render('matuckLibraryBundle:Catalog:seriesbooks.atom.twig', array(
            'title' => $title,
            'author' => $author,
            'email' => $email,
            'series' => $bookseries,
            'books' => $books,
        ));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    /////////////////////////////////////
    //////////////////////////////////////
    ///////////////////////////////////
    //////////////////////////////////
    
    public function tagsletterAction($letter)
    {
        $title = $this->container->getParameter('matuck_library_sitetitle');
        $author = $this->container->getParameter('matuck_library_catalogauthor');
        $email = $this->container->getParameter('matuck_library_catalogemail');
        
        $em = $this->getDoctrine()->getManager();
        $tagrepo = $em->getRepository('matuckLibraryBundle:Tag');
        /* @var $tagrepo \matuck\LibraryBundle\Entity\Tagrepository */
        switch($letter)
        {
            case 'all':
                $tags = $tagrepo->findAllPagerOrderbyName();
                break;
            case 'number':
                $tags = $tagrepo->findByBookswithNumber();
                break;
            default:
                $tags = $tagrepo->findByFirstLetterPaged($letter);
                break;
        }
        /* @var $tags Pagerfanta */
        $tags->setMaxPerPage(10);
        if($this->getRequest()->get('page'))
        {
            $tags->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $tags->setCurrentPage(1);
        }
        $response = $this->render('matuckLibraryBundle:Catalog:tagslist.atom.twig', array(
            'title' => $title,
            'author' => $author,
            'email' => $email,
            'tags' => $tags,
            'letter' => $letter,
        ));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    public function tagbooksAction($id)
    {
        $title = $this->container->getParameter('matuck_library_sitetitle');
        $author = $this->container->getParameter('matuck_library_catalogauthor');
        $email = $this->container->getParameter('matuck_library_catalogemail');
        
        $em = $this->getDoctrine()->getManager();
        $tagrepo = $em->getRepository('matuckLibraryBundle:Tag');
        $taggingrepo = $em->getRepository('matuckLibraryBundle:Tagging');
        $bookrepo = $em->getRepository('matuckLibraryBundle:Book');
        /* @var $tagrepo \matuck\LibraryBundle\Entity\Tagrepository */
        /* @var $taggingrepo \matuck\LibraryBundle\Entity\Taggingrepository */
        /* @var $bookrepo \matuck\LibraryBundle\Entity\Bookrepository */
        $booktag = $tagrepo->find($id);
        $resources = $taggingrepo->findResourcesByTypeandTag('book', $booktag);
        $resources->setMaxPerPage(10);
        if($this->getRequest()->get('page'))
        {
            $resources->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $resources->setCurrentPage(1);
        }
        /* @var $resources \Pagerfanta\Pagerfanta */
        //build array of resources ids
        $resourceids = array();
        foreach($resources->getCurrentPageResults() as $resource)
        {
            /* @var $resource \matuck\LibraryBundle\Entity\Tagging */
            $resourceids[] = $resource->getResourceId();
        }
        $books = $bookrepo->findBooksinArrayofIDs($resourceids);
        $response = $this->render('matuckLibraryBundle:Catalog:tagbooks.atom.twig', array(
            'title' => $title,
            'author' => $author,
            'email' => $email,
            'tag' => $booktag,
            'books' => $books,
            'pager' => $resources,
        ));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    public function searchAction($terms)
    {
        $title = $this->container->getParameter('matuck_library_sitetitle');
        $author = $this->container->getParameter('matuck_library_catalogauthor');
        $email = $this->container->getParameter('matuck_library_catalogemail');
        
        $em = $this->getDoctrine()->getManager();
        $books = $em->getRepository('matuckLibraryBundle:Book')->searchEverything(urldecode($terms));
        if($this->getRequest()->get('page'))
        {
            $books->setCurrentPage($this->getRequest()->get('page'));
        }
        else
        {
            $books->setCurrentPage(1);
        }
        /* @var $books \Pagerfanta\Pagerfanta */
        
        return $this->render('matuckLibraryBundle:Catalog:search.atom.twig', array(
            'title' => $title,
            'author' => $author,
            'email' => $email,
            'books' => $books,
            'terms' => $terms,
        ));
    }
    
    /**
     * Renders a view.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Response $response   A response instance
     *
     * @return Response A Response instance
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        if($response == null)
        {
            $response = new Response();
        }
        $response->headers->set('Content-Type', 'application/atom+xml');
        return parent::render($view, $parameters, $response);
    }
}
