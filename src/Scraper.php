<?php
namespace Government;

require 'vendor/autoload.php';

use Goutte\Client;
use Exception;

/**
* Scraper is used to pull a list of all existing
* government users of GitHub from the government.github.com site
*/
class Scraper
{
    /**
     * crawl a site
     * @param  string $url
     * @return [type]      [description]
     */
    public function crawlSite($url)
    {
        $agencies = array();
        $client = $this->getGoutteClient();
        $crawler = $client->request('GET', $url);
        $children = $crawler->filter('#type-us-federal');
        return $children->text();
    }
    /**
     * [returnResultsArray description]
     * @return [type] [description]
     */
    public function returnResultsArray()
    {
        $results = $this->crawlSite('https://government.github.com/community/');
        $pattern = "/@(\w+-|\w+)+/";
        preg_match_all($pattern, (string) $results, $matches);
        return $matches[0];
    }

    /**
     *
     * @return [type] [description]
     */
    public function getGoutteClient()
    {
        return new Client();
    }
}