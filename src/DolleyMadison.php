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
    use HelperTrait;

    protected $username = '';
    protected $oauthToken = '';
    protected $baseUri = "https://api.github.com";
    /**
     * Constructor sets security keys to values set in envvars
     */
    public function __construct()
    {
        $this->username = getenv('GH_USERNAME'); // Your username
        $this->oauthToken = getenv('GH_OAUTH_KEY'); // Your ouath key
    }

    /**
     * Loop through orgs; fork their repos
     */
    public function execute()
    {
        $orgNames = $this->returnOrgs();
        foreach ($orgNames as $orgName) {
            $orgName = $this->fixOrgName($orgName);
            print "Now forking repos for $orgName organization\n";
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
        foreach ($reposWithPatches as $repo => $ref) {
            $body = array(
                "sha" => $ref,
                "force" => true
            );

            $postData = array('json' => ['sha' => $ref]);

            $res = $this->doAGitHubRequest(
                "PATCH",
                "repos/$this->username/$repo/git/refs/heads/master",
                $postData
            );
            echo "Response is " . json_decode($res->getStatusCode()) . " \n";
        }
    }

    /**
     * [getAllReposToUpdate description]
     * @return [type] [description]
     */
    public function getAllReposToUpdate()
    {
        $orgNames = $this->returnOrgs();
        $reposWithPatches = array();

        foreach ($orgNames as $orgName) {
            $orgName = $this->fixOrgName($orgName);
            $results = $this->gatherMasterRepoInfo($orgName);
            $reposWithPatches[]= $results;
        }
        return $reposWithPatches;
    }

    /**
     * Collect and return array of orgs with repos
     * @param  string $orgName
     * @return array
     */
    public function gatherMasterRepoInfo($orgName)
    {
        $repos = $this->getReposByOrg($orgName);

        $masterFile = array();

        $fp = fopen('orgInfo.csv', 'a');

        foreach ($repos as $repo) {
            try {
                $sha = $this->getUpstreamRepoMaster($orgName, $repo->name);
                $this->writeOrgRepoInfoToCsv($fp, $orgName, $repo->url, $repo->name);
                $masterFile[$repo->name]= $sha;
            } catch(\Exception $e) {
                print "Error getting master branch (one may not exist) {$e->getMessage()}\n";
            }
        }
        fclose($fp);
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
        try {
            $res = $this->doAGitHubRequest("GET", "repos/$orgName/$repo/git/refs/heads/master");
        } catch(\Exception $e) {
            print "Error getting master branch for $orgName/$repo. Trying gh-pages. \n";
            try {
                $res = $this->doAGitHubRequest("GET", "repos/$orgName/$repo/git/refs/heads/gh-pages");
            } catch(\Exception $e) {
                print "Error getting gh-branch. {$e->getMessage()}\n";
                print "We need an adult to look at this.\n";
            }
        }

        if (isset($res)) {
            $elements = json_decode($res->getBody());
            print "Returning Upstream sha from " . $elements->object->url . "\n";
            return $elements->object->sha;
        }
    }

    /**
     * Get each org's repos
     * @param  string $orgName
     * @return array
     */
    public function getReposByOrg($orgName)
    {
        $res = $this->doAGitHubRequest("GET", "orgs/$orgName/repos");
        return json_decode($res->getBody());
    }

    public function writeOrgRepoInfoToCsv($fp, $orgName, $fork, $url)
    {
        fputcsv($fp, array($orgName, $fork, $url, date('y-m-d')));
    }

    /**
     * [postRepoForks description]
     * @param  [type] $orgName [description]
     * @param  [type] $repo    [description]
     * @return [type]          [description]
     */
    public function postRepoForks($orgName, $repo)
    {
        $res = $this->doAGitHubRequest("POST", "repos/$orgName/$repo/forks");
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
        $fp = fopen('orgInfo.csv', 'a');
        $repos = $this->getReposByOrg($orgName);
        foreach ($repos as $key => $value) {
            if ($value->name) {
                echo "forking $value->name \n";
                $this->postRepoForks($orgName, $value->name);
                $this->writeOrgRepoInfoToCsv($fp, $orgName, $value->url, $value->name);
            }
        }
        fclose($fp);
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

    public function fixOrgName($orgName)
    {
        return preg_replace('/@/', '', $orgName);
    }

}
