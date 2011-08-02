<?php
  class Awoochi {
    // By having a customizable Awoochi endpoint, we have two benefits:
    // 1) We can easily improve or rewrite the API and still completely support
    //    old versions of the API.
    // 2) Anyone can modify the endpoint to run their own (local) server after
    //    the Awoochi code has been open-sourced.
    $awoochi_endpoint = "http://awoochi-api.heroku.com";
    $api_version_num  = 1.0;

    // Retrieve an Awoochi object that can do everything from PHP
    public function __init__($url, $key) {
        authenticate($url, $key);
    }

    // Before we can allow the user to do anything with their newly created
    // Awoochi object, we should verify their credentials upfront to prevent
    // errors later on. The key is continued to be passed with each API call,
    // so if they can authenticate here, they should be able to authenticate
    // everywhere else.
    protected function authenticate($url, $key) {
        $result = post_request($awoochi_endpoint, { 
          "URL" => $url, "Key" => $key, "API" => $api_version_num 
        });

        // The server *should* respond 200 OK, as well as provide a boolean
        // representing whether the authentication was a success.
        return ($result.status === 200 && $result.success);
    }

    // POST data to a URL. This should wrap the native PHP post request stuff
    // in a way to make it really easy to send these requests from the library.
    // This method automatically includes the $url and $key variables into the
    // POST data.
    private function post_request($to, $data) {
        // #todo
    }

    // Perform a GET request on a URL and return the contents returned. This
    // method automatically includes the $url and $key variables into the query
    // string.
    private function get_request($url) {
        // #todo
    }

    /* GET Requests through the API */

    // GET a list of this site's Awoochi users
    public function get_user_list() {
        return get_request($awoochi_endpoint + "/users/list");
    }

    // GET a list of this site's achievements
    public function get_achievement_list() {
        return get_request($awoochi_endpoint + "/achievements/list");
    }

    // GET more detail on a specific user
    public function inspect_user($id) {
        return get_request($awoochi_endpoint + "/users/" + $id);
    }

    // GET more detail on a specific achievement
    public function inspect_achievement($id) {
        return get_request($awoochi_endpoint + "/achievements/" + $id);
    }

    // GET information on the relationship between a user and an achievement
    public function get_relationship($user_id, $achievement_id) {
        return get_request($awoochi_endpoint + "/users/" + $id +
                           "?relationship_with=" = $achievement_id);
    }

    /* POST Requests through the API */

    // Create a new user for this site
    public function create_user($identifier) {
        return post_request($awoochi_endpoint + "/users/new", 
                            { "Identifier" => $identifier });
    }

    // Give an achievement to a user
    public function earn_achievement($user_identifier, $achievement_identifier) {
        return post_request($awoochi_endpoint + "/achievements/new",
                           { "User" => $user_identifier, 
                             "Achievement"
    }

  }


?>
