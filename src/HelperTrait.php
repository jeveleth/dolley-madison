<?php
namespace Government;

use GuzzleHttp\Client;

trait HelperTrait {

    function getCurrentDate()
    {
        date_default_timezone_set('UTC');
        return date('y-m-d');
    }

    function flattenArrayOfRepos($reposWithPatches)
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

    /**
     *
     * @param  string $httpVerb    GET/POST/DELETE/PATCH
     * @param  string $route       e.g., /orgs/$orgName/repos
     * @param  json   $postData    e.g., json => ['sha' => $ref]
     * @return Results of http request
     */
    function doAGitHubRequest($httpVerb, $route, $postData = null)
    {
        $client = $this->getNewClient();

        if ($postData) {
            return $client->request($httpVerb, "$this->baseUri/$route", [
                $postData,
                'auth' => [$this->username, $this->oauthToken]
            ]);
        } else {
            return $client->request($httpVerb, "$this->baseUri/$route", [
                'auth' => [$this->username, $this->oauthToken]
            ]);
        }

    }

    /**
     * Return Guzzle client
     */
    public function getNewClient()
    {
        return new Client();
    }
}