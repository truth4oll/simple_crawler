<?php


include_once __DIR__ . '/crawlers/AliexpressCrawler.php';
include_once __DIR__ . '/crawlers/SoukCrawler.php';


class Crawler
{
    //enabled crawlers
    public $crawlerClasses = [
        'AliexpressCrawler',
        'SoukCrawler',
    ];

    //for calculate in usd
    public $currency_ratio = [
        'USD' => 1,
        'RUB' => 0.013658,
        'EUR' => 1.086765,
        'AED' => 0.272261,
    ];


    /**
     * Search with all enabled crawlers
     * @param string $term
     * @return mixed
     */
    public function search($term)
    {
        $result = [];
        $itemCrawlerClass = [];
        //create each crawler
        foreach ($this->crawlerClasses as $crawlerClass) {
            $itemCrawlerClass[] = new $crawlerClass;
        }
        //execute query in each crawlers
        foreach ($itemCrawlerClass as $item) {
            $result += $item->find($term);
        }

        //save to log
        $this->log(json_encode($result));



        //return sorted result
        return $result = $this->sortByPrice($result);
    }


    /**
     * Get html from remote url
     * @param $url
     * @return mixed
     */
    public function callCurl($url)
    {
        $ch = curl_init();
        if (strtolower((substr($url, 0, 5)) == 'https')) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        //curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (Windows; U; Windows NT 5.0; En; rv:1.8.0.2) Gecko/20070306 Firefox/1.0.0.4");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }


    /**
     * sort array by attribute "price"
     * @param $result
     * @return mixed
     */
    public function sortByPrice($result)
    {
        usort($result, function ($a, $b) {
            if ($a->price == $b->price) {
                return 0;
            }
            return ($a->price > $b->price) ? +1 : -1;
        });
        return $result;
    }

    /**
     * Log result in file
     * Line by line
     * @param $str
     */
    public function log($str){
        $path = __DIR__.'/../log.txt';
        $fp = fopen($path,'a');
        fwrite($fp,$str.PHP_EOL);
    }


}