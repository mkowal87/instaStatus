<?php

namespace App\Helpers\InstagramConnector;
/**
 * Created by PhpStorm.
 * User: mkowal
 * Date: 17.04.2019
 * Time: 21:26
 */
use App\Helpers\InstagramConnector\Endpoints;
use InstagramScraper\Instagram;
use Unirest\Request;

class Connector
{

    const HTTP_NOT_FOUND = 404;
    const HTTP_OK = 200;
    const HTTP_FORBIDDEN = 403;
    const HTTP_BAD_REQUEST = 400;


    private $userSession;
    private $userAgent;

    /**
     * @param $session
     * @param $gisToken
     *
     * @return array
     */
    private function generateHeaders($session, $gisToken = null)
    {
        $headers = [];
        if ($session) {
            $cookies = '';
            foreach ($session as $key => $value) {
                $cookies .= "$key=$value; ";
            }

            $csrf = empty($session['csrftoken']) ? $session['x-csrftoken'] : $session['csrftoken'];

            $headers = [
                'cookie' => $cookies,
                'referer' => Endpoints::BASE_URL . '/',
                'x-csrftoken' => $csrf,
            ];

        }

        if ($this->getUserAgent()) {
            $headers['user-agent'] = $this->getUserAgent();

            if (!is_null($gisToken)) {
                $headers['x-instagram-gis'] = $gisToken;
            }
        }

        return $headers;
    }

    /**
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param $userAgent
     *
     * @return string
     */
    public function setUserAgent($userAgent)
    {
        return $this->userAgent = $userAgent;
    }

    /**
     * @param $rawBody
     * @return mixed
     */
    private function decodeRawBodyToJson($rawBody)
    {
        return json_decode($rawBody, true, 512, JSON_BIGINT_AS_STRING);
    }

    /**
     * @param $tag
     * @param int $count
     * @param string $maxId
     * @param null $minTimestamp
     * @return array|int
     */
    public function getNumberOfImagesByTags($tag, $count = 12, $maxId = '', $minTimestamp = null)
    {
        $index = 0;
        $medias = [];
        $mediaIds = [];
        $hasNextPage = true;
        while ($index < $count && $hasNextPage) {
            $response = Request::get(Endpoints::getMediasJsonByTagLink($tag, $maxId),
                $this->generateHeaders($this->userSession));
            if ($response->code !== static::HTTP_OK) {
                return 0;
                // throw new InstagramException('Response code is ' . $response->code . '. Body: ' . static::getErrorBody($response->body) . ' Something went wrong. Please report issue.', $response->code);
            }

            $this->parseCookies($response->headers);

            $arr = $this->decodeRawBodyToJson($response->raw_body);

            if (!is_array($arr)) {
                throw new InstagramException('Response decoding failed. Returned data corrupted or this library outdated. Please report issue');
            }
            if (empty($arr['graphql']['hashtag']['edge_hashtag_to_media']['count'])) {
                return [];
            }

            $countImages = $arr['graphql']['hashtag']['edge_hashtag_to_media']['count'];

            return $countImages;
        }
        return $medias;
    }



    /**
     * We work only on https in this case if we have same cookies on Secure and not - we will choice Secure cookie
     *
     * @param array $headers
     *
     * @return array
     */
    private function parseCookies($headers)
    {
        $rawCookies = isset($headers['Set-Cookie']) ? $headers['Set-Cookie'] : (isset($headers['set-cookie']) ? $headers['set-cookie'] : []);

        if (!is_array($rawCookies)) {
            $rawCookies = [$rawCookies];
        }

        $not_secure_cookies = [];
        $secure_cookies = [];

        foreach ($rawCookies as $cookie) {
            $cookie_array = 'not_secure_cookies';
            $cookie_parts = explode(';', $cookie);
            foreach ($cookie_parts as $cookie_part) {
                if (trim($cookie_part) == 'Secure') {
                    $cookie_array = 'secure_cookies';
                    break;
                }
            }
            $value = array_shift($cookie_parts);
            $parts = explode('=', $value);
            if (sizeof($parts) >= 2 && !is_null($parts[1])) {
                ${$cookie_array}[$parts[0]] = $parts[1];
            }
        }

        $cookies = $secure_cookies + $not_secure_cookies;

        if (isset($cookies['csrftoken'])) {
            $this->userSession['csrftoken'] = $cookies['csrftoken'];
        }

        return $cookies;
    }

    public function getInstagramTagInfo($tagName){

        Request::verifyPeer(false);
        $account = $this->getNumberOfImagesByTags('dupa');

        var_dump($account);die();

        $tagInfo = $this->fetch($this->tagUrl.$tagName.'?__a=1');
var_dump($tagInfo);die();
        return $tagInfo;
    }
    /**
     * Fetch the Instagram feed from API
     * @param  String $url URL to be fetching
     * @return JSON      JSON result from Instagram
     */
    protected function fetch($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    protected function scrap($url){
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

        $page = curl_exec($curl);

        if(curl_errno($curl)) // check for execution errors
        {
            echo 'Scraper error: ' . curl_error($curl);
            exit;
        }

        curl_close($curl);

        $regex = '/<div id="case_textlist">(.*?)<\/div>/s';
        if ( preg_match($regex, $page, $list) )
            echo $list[0];
        else
            print "Not found";
    }
    /**
     * Get a feed from an instagram username
     * Will attempt to get the id based on API search,
     * but that does not always return the correct result because
     * the API uses fuzzy search. In that case, scrape the actual
     * page for the data, but only as a fallback
     *
     * @param  String $user_name Instagram username
     * @return Array
     */
    public function getFeedByUserName($user_name) {
        // Try getting the ID the legit way
        $user_id = $this->getUserID($user_name);

        // If it is null, then try scraping
        if(is_null($user_id)) {
            $user_id = $this->scrapeForID($user_name);
        }

        // Now get the feed
        $this->feed = $this->getFeedByID($user_id);

        return $this->feed;
    }


    /**
     * Get a user ID from username via API
     * @param  String $username username to search
     * @return Int           Instagram userid
     */
    private function getUserID($username) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_URL, "https://api.instagram.com/v1/users/search?q=$username&client_id=".$this->client_id);
        $returned_users = curl_exec($ch);
        curl_close($ch);

        // Loop through the results and find the exact match
        foreach (json_decode( $returned_users )->data as $row) {
            if($row->username == $username) {
                $user_id = $row->id;
                continue;
            }
        }

        return $user_id;
    }

    /**
     * Scrape the contents of the actual Instagram HTML
     * Use this as a fallback, because it may change and break
     * Pulled from https://gist.github.com/cosmocatalano/4544576
     *
     * @param  String $username Instagram username
     * @return Int           Instagram userid
     */
    private function scrapeForID($username) {
        $insta_source = file_get_contents('http://instagram.com/'.$username);
        $shards = explode('window._sharedData = ', $insta_source);
        $insta_json = explode(';</script>', $shards[1]);
        $insta_array = json_decode($insta_json[0], TRUE);

        return $insta_array['entry_data']['ProfilePage'][0]['user']['id'];
    }


}