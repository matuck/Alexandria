<?php
namespace matuck\LibraryBundle\Lib\Filehandler;

interface FilehandlerInterface
{
    public function __construct($container);
    public function moveBook($book, $id);
    public function moveCover($cover, $id);
    public function getBook($id);
    public function getCover($id);
    public function deleteBook($id);
    public function deleteCover($id);
}