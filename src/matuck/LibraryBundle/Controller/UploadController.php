<?php

namespace matuck\LibraryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use matuck\LibraryBundle\Entity\Book;
use matuck\LibraryBundle\Form\BookType;
use matuck\LibraryBundle\Entity\Author;
use matuck\LibraryBundle\Entity\Serie;
use matuck\LibraryBundle\Lib\Filehandler;
use Ivory\LuceneSearchBundle\Model\Document;
use Ivory\LuceneSearchBundle\Model\Field;

class UploadController extends Controller
{
    public function indexAction()
    {
        $form = $this->createFormBuilder()
                ->add('file', 'file', array('label' => 'Filename'))
                ->getForm();
        $response = $this->render('matuckLibraryBundle:Upload:index.html.twig', array('form' => $form->createView()));
        $response->setPublic();
        $response->setSharedMaxAge($this->container->getParameter('cache_time'));
        return $response;
    }

    public function fileAction()
    {
      $file = $this->getRequest()->files->get('form')['file'];
      /* @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
      $tempfilename = uniqid();
      $tempuploadpath = $this->container->getParameter('matuck_library_tempuploads');
      $file->move($tempuploadpath, $tempfilename);
      chmod($tempuploadpath.$tempfilename, 0755);

      
      // 2. Parse the file to get basic information
      $info = $this->getEpubInfo($tempuploadpath.$tempfilename);
      $info['cover'] = '';
      // 2.5 get date from googlebooks api
      $googleapi = $this->get('matuck_library.googlebookapi');
      /* @var $googleapi  \matuck\LibraryBundle\Lib\GoogleBookApi */
      if(isset($info['isbn']) && $info['isbn'] != '')
      {
        $ginfo = $googleapi->fetchMetaByISBN($info['isbn']);
      }
      else if(isset($info['title']) && $info['title'] != '' && isset($info['author']) && $info['author'] != '')
      {
        $ginfo = $googleapi->fetchMetaByTitleAndAuthor($info['title'], $info['author']);
      }
      if(isset($ginfo))
      {
        if(isset($ginfo['title']) && $ginfo['title'] != '')
        {
          $info['title'] = $ginfo['title'];
        }
        if(isset($ginfo['author']) && $ginfo['author'] != '')
        {
          $info['author'] = $ginfo['authors'][0];
        }
        if(isset($ginfo['description']) && $ginfo['description'] != '')
        {
          $info['summary'] = $ginfo['description'];
        }
        if(isset($ginfo['industryIdentifiers'][0]['identifier']) && $ginfo['industryIdentifiers'][0]['identifier'] != '')
        {
          $info['isbn'] = $ginfo['industryIdentifiers'][0]['identifier'];
        }
        if(isset($ginfo['imageLinks']['thumbnail']) && $ginfo['imageLinks']['thumbnail'] != '')
        {
          $info['cover'] = $ginfo['imageLinks']['thumbnail'];
        }
        else
        {
            $info['cover'] = '';
        }
      }
      $nameparser = $this->get('matuck_library.nameparser');
      /* @var $nameparser \matuck\LibraryBundle\Lib\nameparser */
      try
      {
         $info['author'] = $nameparser->lastfirst($info['author']);
      }
      catch(Exception $e)
      {
          $info['author'] = $info['author'];
      }
      
      if(isset($info['title']) && $info['title'] != '')
      {
        if(preg_match('/^The /', $info['title']))
        {
            $info['title'] = preg_replace('/^The /', '', $info['title']).', The';
        }
      }
      if(isset($info['series']))
      {
        //if series begins with the move to end.
        if(preg_match('/^The /', $info['series']))
        {
            $info['series'] = preg_replace('/^The /', '', $info['series']).', The';
        }
      }
      // 3. Return Book Info
      $info['file_id'] = $tempfilename;
      $form = $this->createFormBuilder($info)
              ->add('title', 'text')
              ->add('author', 'text')
              ->add('series', 'text', array('required' => false))
              ->add('series_order', 'text', array('required' => false))
              ->add('isbn', 'text', array('required' => false))
              ->add('summary', 'textarea', array('required' => false))
              ->add('newcover', 'file', array('required' => false))
              ->add('file_id', 'hidden')
              ->add('cover', 'hidden', array('required' => false));
      if($this->container->getParameter('matuck_library_usecaptchas'))
      {
          $form->add('captcha', 'captcha');
      }
      return $this->render('matuckLibraryBundle:Upload:file.html.twig', array('form' => $form->getForm()->createView(), 'cover' => $info['cover']));
    }

    /**
     * Execute submit action
     */
    public function submitAction()
    {
        $indexer = $this->get('matuck_library.searchindexer');
        /* @var $indexer \matuck\LibraryBundle\Lib\Indexer */
        
        $info = $this->getRequest()->request->get('form');
        $em = $this->getDoctrine()->getManager();
        $book = new Book();
        if(!$author = $em->getRepository('matuckLibraryBundle:Author')->findOneByName($info['author']))
        {
            $author = new Author();
            $author->setName($info['author']);
            $author->setCreatedAt(new \DateTime);
            $author->setUpdatedAt(new \DateTime);
            $em->persist($author);
            $em->flush();
            $indexer->indexAuthor($author);
        }
        $book->setAuthor($author);
        if($info['series'] != NULL && $info['series'] != '')
        {
            if(!$serie = $em->getRepository('matuckLibraryBundle:Serie')->findOneByName($info['series']))
            {
                $serie = new Serie();
                $serie->setName($info['series']);
                $serie->setCreatedAt(new \DateTime);
                $serie->setUpdatedAt(new \DateTime);
                $em->persist($serie);
                $em->flush();
                $indexer->indexSeries($serie);
            }
            $book->setSerie($serie);
            $book->setSerieNbr($info['series_order']);
        }
        $book->setIsbn($info['isbn']);
        $book->setTitle($info['title']);
        $book->setSummary($info['summary']);
        $book->setIsPublic(TRUE);
        $book->setCreatedAt(new \DateTime);
        $book->setUpdatedAt(new \DateTime);
        $em->persist($book);
        $em->flush();
        
        $indexer->indexBook($book);
        
        $fh = $this->get('matuck_library.filehandler');
        /* @var $fh Filehandler */
        $cover = FALSE;
        if($file = $this->getRequest()->files->get('form')['newcover'])
        {
            /* @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
            $file->move($this->container->getParameter('matuck_library_tempuploads'), $info['file_id'].'.cover');
            $cover = $info['file_id'].'.cover';
            $fh->moveCover($this->container->getParameter('matuck_library_tempuploads').$cover, $book->getId());
        }
        else
        {
            
            if(!empty($info['cover']))
            {
                $cover = $info['file_id'].'.jpg';
                $this->save_image_from_web($info['cover'], $this->container->getParameter('matuck_library_tempuploads').$cover);
            }
        }
        if($cover && $cover != '')
        {
            $fh->moveCover($cover, $book->getId());
        }

        $fh->moveBook($info['file_id'], $book->getId());

        return $this->redirect($this->generateUrl('matuck_library_book_show', array('id' => $book->getId())));
    }

    /**
     * Get data from content.opf embeded in the epub
     * @param string $filePath path to epub file
     * @return array with data obtained from epub.
     */
    private function getEpubInfo($filePath)
    {
      // An Epub is really just a zip file
      $zip = zip_open($filePath);
      while($entry = zip_read($zip))
      {
        $zipentries[zip_entry_name($entry)] = $entry;
      }
      if(!zip_entry_open($zip, $zipentries['META-INF/container.xml'], "r"))
      {
          throw new Exception('Unable to open container.xml from zip.');
      }
      $container_content = zip_entry_read($zipentries['META-INF/container.xml'], zip_entry_filesize($zipentries['META-INF/container.xml']));
      if(!$container_content)
      {
          throw new Exception('Unable to read the conatainer.xml from the epub');
      }
      zip_entry_close($zipentries['META-INF/container.xml']);
      $content_xml=new \SimpleXMLElement($container_content);
      foreach($content_xml->rootfiles->rootfile as $key =>$rootfile)
      {
        if((string)$rootfile->attributes()->{'media-type'} == 'application/oebps-package+xml')
        {
          $path = (string)$rootfile->attributes()->{'full-path'};
        }
      }
      // Read it
      if(!zip_entry_open($zip, $zipentries[$path], "r"))
      {
          throw new Exception(sprintf('Not able to open the metadata file %s', $zipentries[$path]));
      }
      $entry_content = zip_entry_read($zipentries[$path], zip_entry_filesize($zipentries[$path]));
      if(!$entry_content)
      {
          throw new Exception(sprintf('Not able to read the metadata file %s', $zipentries[$path]));
      }
      // Close the zip
      zip_entry_close($zipentries[$path]);
      zip_close($zip);
      $info = array();
      $content_xml=new \SimpleXMLElement($entry_content);
      $ns = $content_xml->getNamespaces(true);
      if($content_xml->metadata->meta != NULL)
      {
        foreach($content_xml->metadata->meta as $meta)
        {
          switch ($meta['name']) 
          {
            case 'calibre:series':
              $info['series'] = (string)$meta['content'];
              print($info['series']);
              break;

            case 'calibre:series_index':
              $info['series_order'] = (string)$meta['content'];
              break;
          }
        }
      }
      $child = $content_xml->metadata->children($ns['dc']);
      try
      {
          $child->count();
      }
      catch (\Symfony\Component\Debug\Exception\ContextErrorException $e)
      {
          return $info;
      }
      try
      {
          $info['title']=(string)$child->title;
      }
      catch(\Symfony\Component\Debug\Exception\ContextErrorException $e)
      {
          $info['title'] = '';
      }
      try
      {
          $info['author']=(string)$child->creator;
      }
      catch(\Symfony\Component\Debug\Exception\ContextErrorException $e)
      {
          $info['author'] = '';
      }
      try
      {
          $info['summary']=(string)$child->description;
      }
      catch(\Symfony\Component\Debug\Exception\ContextErrorException $e)
      {
          $info['summary'] = '';
      }
      foreach ($child as $node)
      {
        try
        {
            $attrib =$node->attributes($ns['opf']);
            if(strtolower($attrib['scheme']) == 'isbn')
            {
              $info['isbn'] = (string)$node;
            }
        }
        catch(\Symfony\Component\Debug\Exception\ContextErrorException $e)
        {
            
        }
      }
      foreach($child->subject as $cur_subject)
      {
        $info['categories'][]=(string)$cur_subject;
      }
      return $info;

    }
    
    /**
     * Saves an image from the web to a specified path.
     * @param type $url The url where the cover exists on the web
     * @param type $fullpath The full path to save the image to.
     */
    function save_image_from_web($url,$fullpath)
    {
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        $rawdata = curl_exec($ch);
        curl_close($ch);
        if(file_exists($fullpath))
        {
            unlink($fullpath);
        }
        $fp = fopen($fullpath,'x');
        fwrite($fp, $rawdata);
        fclose($fp);
    }
}