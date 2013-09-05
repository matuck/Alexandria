<?php

namespace matuck\LibraryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use matuck\LibraryBundle\Entity\Download;
use Symfony\Component\HttpFoundation\Response;
use Pagerfanta\Pagerfanta;
use matuck\LibraryBundle\Entity\Book;
use matuck\LibraryBundle\Entity\Author;
use matuck\LibraryBundle\Entity\Serie;
use matuck\LibraryBundle\Entity\Tag;
use Symfony\Component\Yaml\Yaml;

class PagesController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $bookrepo = $em->getRepository('matuckLibraryBundle:Book');
        /* @var $bookrepo \matuck\LibraryBundle\Entity\BookRepository */
        $pager = $bookrepo->newestbooks();
        /* @var $pager Pagerfanta */
        $pager->setMaxPerPage(8);
        $newbooks = $pager->getCurrentPageResults();
        $topratedbooks = $bookrepo->findAllPagerOrderbyRating()->setMaxPerPage(5)->getCurrentPageResults();
        $popularbooks = $bookrepo->popularbooks()->setMaxPerPage(10)->getCurrentPageResults();
        $popularauthors = $em->getRepository('matuckLibraryBundle:Author')->findAllPagedOrderbyVotes()->setMaxPerPage(10)->getCurrentPageResults();
        $downloadcount = $em->getRepository('matuckLibraryBundle:Dailydownloads')->getTotalDownloadCount();
        $ratingcount = $em->getRepository('matuckLibraryBundle:Rating')->totalratingcount();
        $authorcount = $em->getRepository('matuckLibraryBundle:Author')->totalauthorcount();
        $populartags = $em->getRepository('matuckLibraryBundle:Tag')->populartags()->setMaxPerPage(20)->getCurrentPageResults();
        //$populartags = $em->getRepository('matuckLibraryBundle:Tag')->populartags()->getQuery()->getMaxResults();
        $featured = $em->getRepository('matuckLibraryBundle:Featured')->PagerOrderbyUpdated()->setMaxPerPage(5)->getCurrentPageResults();
        $bookcount = $bookrepo->totalbookcount();
        $books = $this->container->getParameter('matuck_library_featured');
        $response = $this->render('matuckLibraryBundle:Pages:index.html.twig', array(
            'newbooks' => $newbooks,
            'topratedbooks' => $topratedbooks,
            'popularbooks' => $popularbooks,
            'popularauthors' => $popularauthors,
            'downloadcount' => $downloadcount,
            'ratingcount' => $ratingcount,
            'authorcount' => $authorcount,
            'bookcount' => $bookcount,
            'featuredbooks' => $featured,
            'populartags' => $populartags,
        ));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }
    
    public function templatepageAction($template)
    {
        if($this->get('templating')->exists('matuckLibraryBundle:Pages:'.$template.'.html.twig'))
        {
            $response = $this->render('matuckLibraryBundle:Pages:'.$template.'.html.twig');
            $response->setPublic();
            $response->setSharedMaxAge($this->container->getParameter('cache_time'));
            return $response;
        }
        else
        {
            throw $this->createNotFoundException($template.' page was not found!');
        }
    }

    public function downloadAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        if (!$book = $em->getRepository('matuckLibraryBundle:Book')->find($id))
        {
            throw $this->createNotFoundException('The book you reqeusted could not be found!');
        }
        /* @var $book \matuck\LibraryBundle\Entity\Book */

        // Store the download request
        $download = new Download();
        $download->setBook($book);
        $download->setCreatedAt(new \DateTime);
        $download->setUpdatedAt(new \DateTime);
        $em->persist($download);
        $em->flush();

        $headers = array(
            'Cache-control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Type' => 'application/octet-stream',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Disposition' => 'attachment; filename="'.$book->getDownloadName().'.epub"'
        );  
        $filehandler = $this->get('matuck_library.filehandler');
        if($filehandler->bookExists($book->getId()))
        {
            $response = new Response(file_get_contents($this->get('matuck_library.filehandler')->getBook($book->getId())), 200, $headers);
            $response->setPublic();
            $response->setSharedMaxAge($this->container->getParameter('cache_time'));
            return $response;
        }
        else
        {
            $this->get('session')->getFlashBag()->add('error', 'There book file is missing please flag this book.');
            return $this->redirect($this->generateUrl('matuck_library_book_show', array('id' => $book->getId())));
        }
    }
    
    private function authorform()
    {
        return $this->createFormBuilder()
                ->add('author', 'entity', array(
                    'class' => 'matuckLibraryBundle:Author',
                    'query_builder' => function($er) {
                        return $er->createQueryBuilder('a')
                        ->orderBy('a.name', 'ASC');
                    },
                   'property' => 'name',
                ))
                ->add('type', 'hidden', array(
                    'data' => 'author',
                    ))
                ->getForm();
    }
    
    private function serieform()
    {
        return $this->createFormBuilder()
                ->add('serie', 'entity', array(
                   'class' => 'matuckLibraryBundle:Serie',
                   'query_builder' => function($er) {
                        return $er->createQueryBuilder('s')
                        ->orderBy('s.name', 'ASC');
                    },
                   'property' => 'name',
                ))
                ->add('type', 'hidden', array(
                    'data' => 'serie',
                    ))
                ->getForm();
    }
    
    private function tagform()
    {
        return $this->createFormBuilder()
                ->add('tag', 'entity', array(
                   'class' => 'matuckLibraryBundle:Tag',
                   'query_builder' => function($er) {
                        return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                    },
                   'property' => 'name',
                ))
                ->add('type', 'hidden', array(
                    'data' => 'tag',
                    ))
                ->getForm();
    }
    
    public function browseAction()
    {
        $em = $this->getDoctrine()->getManager();
        $bookcount = $em->getRepository('matuckLibraryBundle:Book')->totalbookcount();
        $authorform = $this->authorform();
        $serieform = $this->serieform();
        $tagform = $this->tagform();
        $response = $this->render('matuckLibraryBundle:Pages:browse.html.twig', array('bookcount' => $bookcount, 'authorform' => $authorform->createView(), 'serieform' => $serieform->createView(), 'tagform' => $tagform->createView()));
        $response->setPublic();
        $response->setSharedMaxAge(600);
        return $response;
    }
    
    public function redirectAction()
    {
        if($this->getRequest()->getMethod() != 'POST')
        {
            throw $this->createNotFoundException("The pages does not exist in this context");
        }
        $form = $this->getRequest()->request->get('form');
        switch($form['type'])
        {
            case 'author':
                return $this->redirect($this->generateUrl('matuck_library_browse_author', array('id' => $form['author'])));
                break;
            case 'serie':
                return $this->redirect($this->generateUrl('matuck_library_browse_serie', array('id' => $form['serie'])));
                break;
            case 'tag':
                return $this->redirect($this->generateUrl('matuck_library_browse_tag', array('id' => $form['tag'])));
                break;
            default:
                throw new $this->createNotFoundException('Browse form did not get submitted properly would route to a page that doesn\'t exist');
                break;
        
        }
    }
}
