<?php
namespace matuck\LibraryBundle\Lib\Filehandler;

use Symfony\Component\DependencyInjection\ContainerAware;
use matuck\LibraryBundle\Lib\Filehandler\FilehandlerInterface;
use Symfony\Component\Filesystem\Exception\IOException;

class Filesystem extends ContainerAware implements FilehandlerInterface
{
    private $tempuploadpath;
    private $bookpath;
    private $coverpath;
    protected $container;
    
    public function __construct($container)
    {
        $this->container = $container;
        $this->tempuploadpath = $this->container->getParameter('matuck_library_tempuploads');
        $this->bookpath = $this->container->getParameter('matuck_library_filehandler_bookpath');
        $this->coverpath = $this->container->getParameter('matuck_library_filehandler_coverpath');
        if(!is_dir($this->bookpath))
        {
            mkdir($this->bookpath, 0755);
        }
        if(!is_dir($this->coverpath))
        {
            mkdir($this->coverpath, 0755);
        }
    }


    public function moveBook($book, $id)
    {
        if(file_exists($this->tempuploadpath.$book))
        {
            if (copy($this->tempuploadpath.$book, $this->bookpath.$id.'.epub'))
            {
              unlink($this->tempuploadpath.$book);
              return true;
            }
            else
            {
                throw new IOException(sprintf('Error copying file %s to %s', $this->tempuploadpath.$book, $this->bookpath));
            }
        }
        else
        {
            throw new IOException('File is not in the temp upload folder.');
        }
    }
    
    public function moveCover($cover, $id)
    {
        if(file_exists($this->tempuploadpath.$cover))
        {
            $im = new \Imagick();
            $im->readimage($this->tempuploadpath.$cover);
            $im->setimageformat('png');
            $im->thumbnailImage(112, 162);
            if($im->writeimage($this->coverpath.$id.'.png'))
            {
                chmod($this->coverpath.$id.'.png', 0755);
                unlink($this->tempuploadpath.$cover);
            }
            else
            {
                throw new IOException('There was an error saving the cover file.');
            }
        }
    }
    
    public function getBook($id)
    {
        return str_replace('{id}', $id, $this->container->getParameter('matuck_library_filehandler_bookurl'));
    }
    
    public function getCover($id)
    {
        return str_replace('{id}', $id, $this->container->getParameter('matuck_library_filehandler_coverurl'));
    }
    
    public function deleteBook($id)
    {
        if(file_exists($this->bookpath.$id.'.epub'))
        {
            if(unlink($this->bookpath.$id.'.epub'))
            {
                return true;
            }
            else
            {
                throw new IOException(sprintf('There was an error deleting the book file %d', $id));
            }
        }
        else
        {
            return true;
        }
    }
    
    public function deleteCover($id)
    {
        if(file_exists($this->coverpath.$id.'.png'))
        {
            if(unlink($this->coverpath.$id.'.png'))
            {
                return true;
            }
            else
            {
                throw new IOException(sprintf('There was an error deleting the cover file for book %d', $id));
            }
        }
        else
        {
            return true;
        }
    }
    
    public function bookExists($id)
    {
        $path = $this->getBook($id);
        if(file_exists($path))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
    
    public function coverExists($id)
    {
        $path = $this->getCover($id);
        if(file_exists($path))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
}
?>