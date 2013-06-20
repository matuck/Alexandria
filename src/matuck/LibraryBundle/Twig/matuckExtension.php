<?php
namespace matuck\LibraryBundle\Twig;

use matuck\LibraryBundle\Lib\Filehandler\Filehandler;

class matuckExtension extends \Twig_Extension
{
    private $fh;
 
    public function __construct(Filehandler $fh) {
        $this->fh = $fh;
    }
    
    public function getFilters()
    {
        return array(
            'numbertotext' => new \Twig_Filter_Method($this, 'numbertotextFilter'),
        );
    }
    
    public function getFunctions()
    {
        return array(
            'coverurl' => new \Twig_Function_Method($this, 'coverurl'),
        );
    }

    public function numbertotextFilter($number)
    {
        if($number >=0 && $number < 100)
        {
            $result = $number;
        }
        else if ($number >= 100 && $number <= 999)
        {
            $result =substr($number, 0, -2) . " Hundred";
        }
        else if ($number >= 1000 && $number <= 999999)
        {
            $result =substr($number, 0, -3) . " Thousand";
        }
        else if ($number >= 1000000 && $number <= 999999999)
        {
            $first =substr($number, 0, -6);
            $second =substr($number, 1, -4);
            $result = $first . "." . $second . " Million";
        }
        return $result;
    }
    
    public function coverurl($id)
    {
        return $this->fh->getCover($id);
    }

    public function getName()
    {
        return 'matuck_extension';
    }
}
?>