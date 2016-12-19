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
     * Crawl a site
     * @param  string $url
     * @return array
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
     * Return list of government orgs using GitHub
     * @return array
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
     * @return Crawler instance
     */
    public function getGoutteClient()
    {
        return new Client();
    }
}