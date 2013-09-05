<?php
namespace matuck\LibraryBundle\Lib;

use Symfony\Component\HttpFoundation\Request;


class IpHash
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function get()
    {
        if(!$this->request->attributes->has('iphash'))
        {
            $ip = $this->request->getClientIp();
            $ip2 = explode('.', $ip);
            $somehash = md5($ip2[2].$ip2[3].$ip);
            $this->request->attributes->add(array('iphash' => $somehash));
        }
        return $this->request->attributes->get('iphash');
    }
}
?>
