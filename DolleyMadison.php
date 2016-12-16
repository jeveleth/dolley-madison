<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
/**
 * Use this script to fork all the GitHub repos of government organizations
 */
class DolleyMadison
{
    protected $username = '';
    protected $oauthToken = '';
    protected $baseUri = "https://api.github.com";

    /**
     * constructor sets security keys to values set in envvars
     */
    public function __construct()
    {
        $this->username = getenv('GH_USERNAME'); // Your username
        $this->oauthToken = getenv('GH_OUATH_KEY'); // Your ouath key
    }

    /**
     * [getNewClient description]
     * @return [type] [description]
     */
    public function getNewClient()
    {
        return new Client();
    }

    /**
     * [getReposByOrg description]
     * @param  [type] $orgName [description]
     * @return [type]          [description]
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
}

$gd = new GovernmentData();
// TODO: Pull Names of all government entities with repos on github.
// TODO: Create ReadMe
// TODO: Error handling

$orgNames = array('usinterior', 'GSA');
foreach ($orgNames as $orgName) {
    $gd->forkReposByOrg($orgName);
}
