<?php

/**
 * Created by PhpStorm.
 * User: mkowal
 * Date: 17.04.2019
 * Time: 21:26
 */
class InstagramConnector
{
    public $result;
    public static $display_size = 'thumbnail'; // you can choose between "low_resolution", "thumbnail" and "standard_resolution"
    public static $user_id;
    public $feed;
    private $offset_media_id;
    private $count;
    private $next_max_id;

    private $client_id = "XXXXXX";
    private $client_secret = "XXXXXX";

    function __construct($count, $offset) {
        $this->offset_media_id = $offset;
        $this->count = $count;
    }

    /**
     * Fetch the Instagram feed from API
     * @param  String $url URL to be fetching
     * @return JSON      JSON result from Instagram
     */
    public static function fetch($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
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
     * Return a feed based on tag name
     * @param  String $tag_name The tag
     * @return JSON
     */
    public function getFeedByTagName($tag_name) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        if($this->offset_media_id == 0) {
            curl_setopt($ch, CURLOPT_URL, "https://api.instagram.com/v1/tags/$tag_name/media/recent/?count=$this->count&client_id=".$this->client_id);
        }
        else {
            curl_setopt($ch, CURLOPT_URL, "https://api.instagram.com/v1/tags/$tag_name/media/recent/?count=$this->count&max_tag_id=$this->offset_media_id&client_id=".$this->client_id);
        }

        $posts = curl_exec($ch);
        curl_close($ch);

        $posts = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
        }, $posts);

        $posts = json_decode(html_entity_decode(utf8_encode($posts)));

        // There was an error, return the error message
        if($posts->meta && $posts->meta->error_type) {
            return $posts->meta->error_message;
        }

        $this->next_max_id = $posts->pagination->next_max_id;
        $posts = $posts->data;

        $posts = $this->getStandardizedData($posts);

        return $posts;
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


    /**
     * Query the Instagram API for a user's feed
     * @param  Int $user_id Instagram userid
     * @return JSON
     */
    private function getFeedByID($user_id) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        if($this->offset_media_id == 0) {
            curl_setopt($ch, CURLOPT_URL, "https://api.instagram.com/v1/users/$user_id/media/recent/?count=$this->count&client_id=".$this->client_id);
        }
        else {
            curl_setopt($ch, CURLOPT_URL, "https://api.instagram.com/v1/users/$user_id/media/recent/?count=$this->count&max_id=$this->offset_media_id&client_id=".$this->client_id);
        }

        $posts = curl_exec($ch);
        curl_close($ch);

        $posts = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
        }, $posts);

        $posts = json_decode(html_entity_decode(utf8_encode($posts)));
        $this->next_max_id = $posts->pagination->next_max_id;
        $posts = $posts->data;

        $posts = $this->getStandardizedData($posts);

        return $posts;
    }

    /**
     * Get the next pagination ID
     * @return Int
     */
    public function getNextMaxID() {
        return $this->next_max_id;
    }

    /**
     * Standardize the feed from Instagram
     * @param  Obj $data Instagram API data
     * @return Array
     */
    private function getStandardizedData($data) {
        $newData = array();

        foreach($data as $post) {
            $images = array();

            array_push($images, $post->images->low_resolution);
            array_push($images, $post->images->thumbnail);
            array_push($images, $post->images->standard_resolution);

            $alt_sizes = array('alt_sizes' => $images);

            array_push($newData, array(
                'post_url' => $post->link,
                'photos' => array($alt_sizes),
                'feed_source' => "instagram",
                'id' => $post->id,
                'title' => $post->user->username,
                'feed_offset' =>$this->offset_media_id,
                'next_max_id' =>$this->next_max_id,
                'timestamp' => $post->created_time
            ));
        }

        return $newData;

    }


}