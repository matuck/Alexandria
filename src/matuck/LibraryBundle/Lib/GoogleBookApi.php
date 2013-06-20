<?php
namespace matuck\LibraryBundle\Lib;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GoogleBookApi extends ContainerAware
{
    protected $container;
    private $keys;
    private $usedkey;
    
    public function __construct($container)
    {
        $this->container = $container;
        $this->keys = $this->container->getParameter('matuck_library_googleapi_keys');
    }
    
    /**
     * Get data from a web page with error code
     * @param string $url the url to fetch
     * @return array keys status for error message and content for the data retrieved.
     */
    private function curlGet($url)
    {
      $c = curl_init($url);
      curl_setopt($c,CURLOPT_RETURNTRANSFER, true);
      curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
      $html = curl_exec($c);
      if (curl_error($c))
      die(curl_error($c));
      // Get the status code
      $status = curl_getinfo($c, CURLINFO_HTTP_CODE);
      curl_close($c);
      sleep(2);
      return array('status' => $status, 'content' => $html);
    }

    /**
     * Append google api key to a url
     * @param string $url url to append the key to.
     * @return string return url with key appened. 
     */
    private function appendGoogleAPIKey($url)
    {
        $key =  array_rand($this->keys);
        $this->usedkey = $this->keys[$key];
        if(preg_match('/\?/',$url))
        {
            return $url."&key=".$this->usedkey;
        }
        else
        {
            return $url."?key=".$this->usedkey;
        }
    }

    private function getbookData($url)
    {
        $curl=$this->curlGet($this->appendGoogleAPIKey($url));
        if($curl['status']==200)
        {
            $json=json_decode($curl['content'],true);
            $bookurl=$this->appendGoogleAPIKey('https://www.googleapis.com/books/v1/volumes/'.$json['items'][0]['id']);
            $curl=$this->curlGet($bookurl);
            if($curl['status']==200)
            {
              $json=json_decode($curl['content'],true);
              return $json['volumeInfo'];
            }
            else
            {
                $error = json_decode($curl['content'])->error;
                throw new HttpException($error->code, $error->errors[0]->message.' because '.$error->errors[0]->reason.' and '.$error->errors[0]->domain.' key used: '.$this->usedkey);
            }
        }
        else
        {
            $error = json_decode($curl['content'])->error;
            throw new HttpException($error->code, $error->errors[0]->message.' because '.$error->errors[0]->reason.' and '.$error->errors[0]->domain.' key used: '.$this->usedkey);
        }
    }
    /**
     * Get book data from google by ISBN
     * @param string $isbn the isbn of the book
     * @return false|array array with data of book false on failure 
     */
    public function fetchMetaByISBN($isbn)
    {
        return $this->getbookData('https://www.googleapis.com/books/v1/volumes?q=isbn:'.$isbn);
    }

    /**
     * Get book data from google by Title and Author
     * @param string $title the title of the book
     * @param string $author the author of the book
     * @return false|array array with data of book false on failure 
     */
    public function fetchMetaByTitleAndAuthor($title,$author)
    {
        $urlEncodedTitle = urlencode($title);
        $urlEncodedAuthor = urlencode($author);
        return $this->getbookData('https://www.googleapis.com/books/v1/volumes?q='.$urlEncodedTitle."+inauthor:".$urlEncodedAuthor);
    }
}
?>