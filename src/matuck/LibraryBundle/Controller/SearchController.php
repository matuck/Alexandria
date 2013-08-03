<?php

namespace matuck\LibraryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SearchController extends Controller
{
    public function indexAction()
    {
        if($this->getRequest()->get('search') == null)
        {
            return $this->render('matuckLibraryBundle:Search:index.html.twig');
        }
        else
        {
            $index = $this->get('ivory_lucene_search')->getIndex('master');
            $results = $index->find($this->getRequest()->get('search'));
            return $this->render('matuckLibraryBundle:Search:index.html.twig', array('search' => $this->getRequest()->get('search'), 'results' => $results));
        }
    }
    
    public function advancedAction()
    {
        $req = $this->getRequest();
        $type = $req->get('type');
        $title = $req->get('title');
        $author = $req->get('author');
        $series = $req->get('series');
        if($type == null || ($title == null && $author == null && $series == null))
        {
            return $this->render('matuckLibraryBundle:Search:advanced.html.twig');
        }
        else
        {
            $params = array();
            $index = $this->get('ivory_lucene_search')->getIndex('master');
            $params['type'] = $type;
            $query = 'type:'.$type;
            if($type == 'book')
            {
                if($title)
                {
                    $query .= ' AND title:'.$title;
                    $params['title'] = $title;
                }
                if($author)
                {
                    $query .= ' AND author:'.$author;
                    $params['author'] = $author;
                }
                if($series)
                {
                    $query .= ' AND series:'.$series;
                    $params['series'] = $series;
                }
            }
            else if ($type == 'author')
            {
                if($author == null)
                {
                    return $this->render('matuckLibraryBundle:Search:advanced.html.twig');
                }
                else
                {
                    $query .= ' AND name:'.$author;
                    $params['author'] = $author;
                }
            }
            else if ($type == 'serie')
            {
                if($series == null)
                {
                    return $this->render('matuckLibraryBundle:Search:advanced.html.twig');
                }
                else
                {
                    $query .= ' AND name:'.$series;
                    $params['series'] = $series;
                }
            }
            
            $params['results'] = $index->find($query);
            return $this->render('matuckLibraryBundle:Search:advanced.html.twig', $params);
        }
    }
    public function helpAction()
    {
        $response = $this->render('matuckLibraryBundle:Search:help.html.twig');
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
}