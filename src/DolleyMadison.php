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
     * [update description]
     * @return [type] [description]
     */
    public function update()
    {
        // Do this for "update" switch command
        $reposWithPatches = $this->getAllReposToUpdate();
        $flattenedArray = $this->flattenArrayOfRepos($reposWithPatches);
        $this->updateForkWithMaster($flattenedArray);
    }

    /**
     * [updateForkWithMaster description]
     * @param  [type] $reposWithPatches [description]
     * @return [type]                   [description]
     */
    public function updateForkWithMaster($reposWithPatches)
    {
        $client = $this->getNewClient();
        foreach ($reposWithPatches as $repo => $ref) {
            $body = array(
                "sha" => $ref,
                "force" => true
            );
            $res = $client->request('PATCH', "$this->baseUri/repos/$this->username/$repo/git/refs/heads/master", [
                'json' => ['sha' => $ref],
                'auth' => [$this->username, $this->oauthToken]
            ]);
            echo "Response is " . json_decode($res->getStatusCode()) . " \n";
        }
    }

    public function flattenArrayOfRepos($reposWithPatches)
    {
        $flattenedArray = array();

        foreach ($reposWithPatches as $datum) {
            if (is_array($datum)) {
                foreach($datum as $key => $value) {
                    print "$key is $value\n";
                    $flattenedArray[$key] = $value;
                }
            }
        }
        return $flattenedArray;
    }

    public function getAllReposToUpdate()
    {
        $orgNames = $this->returnOrgs();
        $reposWithPatches = array();

        foreach ($orgNames as $orgName) {
            $orgName = preg_replace('/@/', '', $orgName);
            $results = $this->gatherMasterRepoInfo($orgName);
            $reposWithPatches[]= $results;
        }
        return $reposWithPatches;
    }

    public function gatherMasterRepoInfo($orgName)
    {
        $repos = $this->getReposByOrg($orgName);

        $masterFile = array();

        foreach ($repos as $repo) {
            try {
                $sha = $this->getUpstreamRepoMaster($orgName, $repo->name);
                $masterFile[$repo->name]= $sha;
            } catch(\Exception $e) {
                print "error getting master branch (one may not exist) {$e->getMessage()}\n";
            }
        }

        return $masterFile;
    }

    /**
     * [getUpstreamRepoMaster description]
     * @param  [type] $orgName [description]
     * @param  [type] $repo    [description]
     * @return [type]          [description]
     */
    public function getUpstreamRepoMaster($orgName, $repo)
    {
        # GET /repos/:owner/:repo/git/refs/:ref
        $client = $this->getNewClient();
        $res = $client->request('GET', "$this->baseUri/repos/$orgName/$repo/git/refs/heads/master", [
            'auth' => [$this->username, $this->oauthToken]
        ]);
        $elements = json_decode($res->getBody());
        return $elements->object->sha;
    }

    /**
     * Get each org's repos
     * @param  string $orgName
     * @return array
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
     * Return array of government orgs
     * @return array
     */
    public function returnOrgs()
    {
        $gs = $this->getNewScraper();
        return $gs->returnResultsArray();
    }

    /**
     * Return instance of Scraper class
     */
    public function getNewScraper()
    {
        return new Scraper();
    }

    /**
     * Return Guzzle client
     */
    public function getNewClient()
    {
        return new Client();
    }


}