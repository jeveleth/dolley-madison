<?php
namespace Government;

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use \Government\Scraper as Scraper;
use Exception;

/**
 * Use this script to fork all the GitHub repos of U.S. government organizations
 */
class DolleyMadison
{
    protected $username = '';
    protected $oauthToken = '';
    protected $baseUri = "https://api.github.com";

    /**
     * Constructor sets security keys to values set in envvars
     */
    public function __construct()
    {
        $this->username = getenv('GH_USERNAME'); // Your username
        $this->oauthToken = getenv('GH_OUATH_KEY'); // Your ouath key
    }

    /**
     * Loop through orgs; fork their repos
     */
    public function execute()
    {
        $orgNames = $this->returnOrgs();
        foreach ($orgNames as $orgName) {
            $orgName = preg_replace('/@/', '', $orgName);
            print "now forking repos for $orgName organization\n";
            try {
                $this->forkReposByOrg($orgName);
            } catch(Exception $e) {
                print "Error getting $orgName: " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * [getReposByOrg description]
     * @param  string $orgName
     * @return
     */
    public function getReposByOrg($orgName)
    {
        $client = $this->getNewClient();
        $res = $client->request('GET', "$this->baseUri/orgs/$orgName/repos", [
            'auth' => [$this->username, $this->oauthToken]
        ]);
        $elements = json_decode($res->getBody());

        return $elements;
    }

    /**
     * [postRepoForks description]
     * @param  [type] $orgName [description]
     * @param  [type] $repo    [description]
     * @return [type]          [description]
     */
    public function postRepoForks($orgName, $repo)
    {
        $client = $this->getNewClient();
        $res = $client->request('POST', "$this->baseUri/repos/$orgName/$repo/forks", [
            'auth' => [$this->username, $this->oauthToken]
        ]);
        $elements = json_decode($res->getStatusCode());
        return "Status is $elements\n";
    }

    /**
     * [forkReposByOrg description]
     * @param  [type] $orgName [description]
     * @return [type]          [description]
     */
    public function forkReposByOrg($orgName)
    {
        $repos = $this->getReposByOrg($orgName);
        foreach ($repos as $key => $value) {
            if ($value->name) {
                echo "forking $value->name \n";
                $this->postRepoForks($orgName, $value->name);
            }
        }
    }

    /**
     * [returnOrgs description]
     * @return [type] [description]
     */
    public function returnOrgs()
    {
        $gs = $this->getNewScraper();
        return $gs->returnResultsArray();
    }

    /**
     * [getNewScraper description]
     * @return [type] [description]
     */
    public function getNewScraper()
    {
        return new Scraper();
    }

    /**
     * [getNewClient description]
     * @return [type] [description]
     */
    public function getNewClient()
    {
        return new Client();
    }


}