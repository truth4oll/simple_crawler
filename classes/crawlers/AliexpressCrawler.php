<?php

class AliexpressCrawler extends Crawler
{

    private $currency = 'RUB';

    /**
     * :keyword: - placeholder for replace
     * @var string
     */
    private $searchUrl = 'http://aliexpress.com/wholesale?catId=0&initiative_id=&SearchText=:keyword:';


    /**
     * @param $term
     * @return array
     */
    public function find($term)
    {
        //prepare url
        $url = str_replace(':keyword:', urlencode($term), $this->searchUrl);
        //get html
        $html = $this->callCurl($url);

        return $this->getItemsByDom($html);
    }

    /**
     * Return items from search page
     * @param $html
     * @return array
     * @internal param DOMXPath $xpath
     */
    public function getItemsByDom($html)
    {
        $result = [];
        //turn off html warnings
        libxml_use_internal_errors(true);


        $doc = new DOMDocument();
        $doc->loadHTML($html);

        $xpath = new DOMXpath($doc);

        //get ul>li with items
        $items = $xpath->query('//*[@id="hs-list-items"]/li[*]');

        foreach ($items as $item) {

            $oProduct = new Product();

            //get name
            if ($nodes = $xpath->query('.//a', $item)) {
                $oProduct->name = $nodes->item(0)->nodeValue;
            }

            if ($nodes = $xpath->query('.//img', $item)) {
                if ($nodes->length > 0) {
                    $oProduct->image_url = $nodes->item(0)->getAttribute('src');
                }
            }

            if ($nodes = $xpath->query('.//*[@class="price price-m"]/span[1]', $item)) {
                if ($nodes->length > 0) {
                    $oProduct->price = $this->preparePrice($nodes->item(0)->nodeValue);
                }

            }

            if ($nodes = $xpath->query('.//*[@class="info infoprice"]//*[@class="original-price"]', $item)) {
                if ($nodes->length > 0) {
                    $oProduct->msrp = $this->preparePrice($nodes->item(0)->nodeValue);
                }
            }

            if ($nodes = $xpath->query('.//*[@class="info infoprice"]//*[@class="new-discount-rate"]', $item)) {
                if ($nodes->length > 0) {
                    $oProduct->percentage = $nodes->item(0)->nodeValue;
                }
            }
            $result[] = $oProduct;
        }


        return $result;
    }

    /**
     * Prepare price for current crawler
     * @param $price
     * @return float|null
     */
    private function preparePrice($price)
    {
        $price = htmlentities($price);
        $price = strtr($price, [',' => '.', '&nbsp;' => '', ' ' => '']);
        preg_match('/(\d+\.\d+)/', $price, $matches);
        $price = (isset($matches[1])) ? $matches[1] : null;

        //convert to usd
        if ($price !== null) {
            $price = $this->currency_ratio[$this->currency] * $price;
            return round($price, 2);
        }
        return null;
    }

}