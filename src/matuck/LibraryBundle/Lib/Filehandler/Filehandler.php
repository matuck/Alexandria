<?php
namespace matuck\LibraryBundle\Lib\Filehandler;

use Symfony\Component\DependencyInjection\ContainerAware;
use matuck\LibraryBundle\Lib\Filehandler\Filesystem;

class Filehandler extends ContainerAware
{
    private $fh;
    protected $container;
    
    public function __construct($container)
    {
        $this->container = $container;
        $handler = $this->container->getParameter('matuck_library_filehandler');
        switch ($handler)
        {
            case 'Filesystem':
                $this->fh = new Filesystem($container);
                break;
            default:
                throw new \Exception('The matuck_library_filehandler variable is not set properly.');
        }
        return $this;
    }

    public function moveBook($book, $id)
    {
        return $this->fh->moveBook($book, $id);
    }
    
    public function moveCover($cover, $id)
    {
        return $this->fh->moveCover($cover, $id);
    }
    
    public function  getBook($id)
    {
        return $this->fh->getBook($id);
    }
    
    public function getCover($id)
    {
        return $this->fh->getCover($id);
    }
    
    public function deleteBook($id)
    {
        return $this->fh->deleteBook($id);
    }
    
    public function deleteCover($id)
    {
        return $this->fh->deleteCover($id);
    }
}
?>