<?php
  // Awoochi Internet Achievements
  // http://www.awoochi.com
  //
  // This script provides a simple interface for use with the Awoochi Internet
  // Achievements platform. Blah blah blah.
  //
  // You are licensed to do whatever you want blah blah blah.
  //
  // PHP Library by Andrew Brown (http://www.drusepth.net)

  class Awoochi {
    // By having a customizable Awoochi endpoint, we have two benefits:
    // 1) We can easily improve or rewrite the API and still completely support
    //    old versions of the API.
    // 2) Anyone can modify the endpoint to run their own (local) server after
    //    the Awoochi server code has been open-sourced.
    $awoochi_endpoint = 'http://awoochi-api.heroku.com';
    $api_version_num  = 1.0;

    // Retrieve an Awoochi object that can do everything from PHP
    public function __init__($url, $key) {
        if (!authenticate($url, $key)) {
            throw new Exception("Invalid key for Awoochi");
        }
    }

    // Before we can allow the user to do anything with their newly created
    // Awoochi object, we should verify their credentials upfront to prevent
    // errors later on. The key is continued to be passed with each API call,
    // so if they can authenticate here, they should be able to authenticate
    // everywhere else.
    protected function authenticate($url, $key) {
        $result = post_request($awoochi_endpoint + "/authenticate", array());

        // The server *should* respond 200 OK, as well as provide a boolean
        // representing whether the authentication was a success.
        return ($result['status'] === 200 && !!$result['success']);
    }

    // POST data to a URL. This should wrap the native PHP post request stuff
    // in a way to make it really easy to send these requests from the library.
    // This method automatically includes the $url, $key, and API version
    // variables into the POST data.
    private function post_request($to, $data) {
        $params = array(
            'http'   => array(
                'method'  => 'POST',
                'content' => $data
            )
        );

        $ctx = stream_context_create($params);
        $fp  = @fopen($url, 'rb', false, $ctx);

        if (!$fp) {
            throw new Exception("Problem with $url: $php_errormsg");
        }

        $response = @stream_get_contents($fp);
        if ($response === false) {
            throw new Exception("Problem reading data from $url: $php_errormsg");
        }

        return json_encode($response);
    }

    // Perform a GET request on a URL and return the contents returned. This
    // method automatically includes the $url and $key variables into the query
    // string. The server responds with a JSON object which this function then
    // returns.
    private function get_request($url) {
        $response = file_get_contents($url);
        return json_encode($response);
    }

    /* GET Requests through the API */

    // GET a list of this site's Awoochi users
    public function get_user_list() {
        return get_request($awoochi_endpoint + '/users/list');
    }

    // GET a list of this site's achievements
    public function get_achievement_list() {
        return get_request($awoochi_endpoint + '/achievements/list');
    }

    // GET more detail on a specific user
    public function inspect_user($id) {
        return get_request($awoochi_endpoint + '/users/' + $id);
    }

    // GET more detail on a specific achievement
    public function inspect_achievement($id) {
        return get_request($awoochi_endpoint + '/achievements/' + $id);
    }

    // GET information on the relationship between a user and an achievement
    public function get_a_relationship($user_id, $achievement_id) {
        return get_request($awoochi_endpoint + '/relationships/get' +
            '?base_user='   + $user_id +
            '?achievement=' + $achievement_id);
    }

    // GET information on the relationship between two users
    public function get_u_relationship($user_id, $other_user_id) {
        return get_request($awoochi_endpoint + '/relationships/get' +
            '?base_user='  + $user_id +
            '?other_user=' + $other_user_id);
    }

    /* POST Requests through the API */

    // Create a new user for this site
    public function create_user($identifier) {
        return post_request($awoochi_endpoint + '/users/new', array(
            'Identifier'  => $identifier
        ));
    }

    // Give an achievement to a user
    public function earn_achievement($user_identifier, $achievement_identifier) {
        return post_request($awoochi_endpoint + '/achievements/new', array(
            'User'        => $user_identifier, 
            'Achievement' => $achievement_identifier
        ));
    }

    /* Awoochi Tracks */
    // The following functions allow you to use Awoochi's remote servers to
    // track the progress of achievements for your users. What this means is that
    // you do not need to store any extra data in your database exclusively for
    // using Awoochi; you can use our handy methods to interact with our
    // database of Achievement Tracks.

    // Create a new "track" for a user towards a given achievement. As the user
    // gets closer to the goal, you can increment the track's progress counter.
    // When a user reaches the $goal value in the counter, they will
    // automatically be awarded the achievement -- no extra work from you required.
    public function create_track($user_id, $achievement_id, $goal) {
        return post_request($awoochi_endpoint + '/track/new', array(
            'User'        => $user_id,
            'Achievement' => $achievement_id,
            'Goal'        => $goal
        );
    }

    // You can manually set a page that we will make a POST request to when a
    // user earns an achievement. The request will include the user's identifier,
    // the achievement identifier, your private key, and the time it was earned.
    public function set_track_callback($user_id, $achievement_id, $callback_url) {
        return post_request($awoochi_endpoint + '/track/update', array(
            'User'        => $user_id,
            'Achievement' => $achievement_id,
            'Callback'    => $callback_url
        );
    }

    // Increment the user's track towards a given achievement by 1. If it would
    // put the user at the track's goal, then the user is awarded the
    // achievement and the track is deleted.
    public function advance_track($user_id, $achievement_id) {
        return post_request($awoochi_endpoint + '/track/advance', array(
            'User'        => $user_id,
            'Achievement' => $achievement_id
        );
    }

    // Reduce a user's progress in a track by 1. Progress cannot go below zero.
    // If this would bring a user's progress to zero, the track is not deleted
    // from the database.
    public function decrement_track($user_id, $achievement_id) {
        return post_request($awoochi_endpoint + '/track/decrement', array(
            'User'        => $user_id,
            'Achievement' => $achievement_id
        );
    }

    // Reset the user's progress in a track to zero, but does not delete the
    // track from the database.
    public function reset_track($user_id, $achievement_id) {
        return post_request($awoochi_endpoint + '/track/reset', array(
            'User'        => $user_id,
            'Achievement' => $achievement_id
        );
    }

    // Delete a track from the database. There is no undo.
    public function delete_track($user_id, $achievement_id) {
        return post_request($awoochi_endpoint + '/track/delete', array(
            'User'        => $user_id,
            'Achievement' => $achievement_id
        );
    }
  }
?>
