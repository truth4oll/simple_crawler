<?php

include_once __DIR__ . '/../Product.php';


class SoukCrawler extends Crawler
{

    private $currency = 'AED';


    /**
     * :keyword: - placeholder for replace
     * @var string
     */
    private $searchUrl = 'http://uae.souq.com/ae-en/:keyword:/s/';

    public function find($term)
    {
        //prepare url
        $url = str_replace(':keyword:', urlencode($term), $this->searchUrl);
        //get html
        $html = $this->callCurl($url);
        return $this->getItemsByDom($html);
    }


    /**
     * @param string $html
     * @return array
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
        $items = $xpath->query('//*[@class="placard"]');


        /** @var DOMElement $item */
        foreach ($items as $item) {
            $oProduct = new Product();

            //get name
            if ($nodes = $xpath->query('.//a[@class="itemLink"]', $item)) {
                if ($nodes->length > 0) {
                    $oProduct->name = $nodes->item(0)->getAttribute('title');
                }
            }

            //get image
            if ($nodes = $xpath->query('.//img', $item)) {
                if ($nodes->length > 0) {
                    $node = $nodes[0];
                    $oProduct->image_url = ($node->hasAttribute('data-src')) ? $node->getAttribute('data-src') : $node->getAttribute('src');
                }
            }

            //get price
            if ($nodes = $xpath->query('.//span[@class="is block"]', $item)) {
                if ($nodes->length > 0) {
                    $oProduct->price = $this->preparePrice($nodes->item(0)->nodeValue);
                }
            }

            //get old price
            if ($nodes = $xpath->query('.//span[@class="was block"]', $item)) {
                if ($nodes->length > 0) {
                    $oProduct->msrp = $this->preparePrice($nodes->item(0)->nodeValue);
                }

            }

            //calculate percentage
            if ($oProduct->price != null && $oProduct->msrp != null) {
                if ($nodes->length > 0) {
                    $oProduct->percentage = 100 - round($oProduct->price / $oProduct->msrp * 100);
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
        $price = strtr($price, [',' => '', '&nbsp;' => '', ' ' => '']);
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