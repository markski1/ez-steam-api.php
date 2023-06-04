<?php

// grab your API key at http://steamcommunity.com/dev/apikey

// you may use this if you wish to always use the same api key,
// otherwise you'll have to specify it whenever you instantiate a SteamRequest object.

$hardcode_api_key = "YOUR_API_KEY"; 

class SteamRequest {

	private $api_key;



	function __construct() {
		$this->api_key = $hardcode_api_key;
	}

}

class SteamUser {
	public $name;
	public $status;
	public $last_seen;
	public $account_created;
	public $playing_game;
	public $avatar_url;
	public $profile_visibility;
	public $steamid;

	function __construct($json) {
		// todo
	}
}

class SteamGame {
	public $name;
	public $price_usd;
	public $playing_right_now;

	function __construct($json) {
		// todo
	}
}

?>