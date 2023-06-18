<?php

/*
 / ez_steam_api.php v0.3 by Markski
 /
 / For updates and documentation: github.com/markski1/ez-Steam-API-PHP
 /
 / For contact: me@markski.ar
 / Website: markski.ar
 /
*/

class SteamRequest
{
	// Will contain the API key to make requests from this object. MUST BE SECRET.
	private $api_key;

	function __construct($set_api_key)
	{
		$this->api_key = $set_api_key;
	}


	/**
	 * Given a URL to a steam profile, returns a SteamID, or false if there's an error.
	 * @param string $url
	 * @return string|bool
	 */
	function ResolveProfileURL($url)
	{
		// first check if it's a vanity URL.
		$find = strpos($url, "/id/");

		if (!$find) {
			// if not, then check for id url
			$find = strpos($url, "/profiles/");

			// if it isn't, fail.
			if (!$find) {
				return false;
			}

			// else, just extract it.
			$SteamID = substr($url, $find + 10);

			// if there's more URL left, remove it.
			$find = strpos($SteamID, "/");

			if ($find) {
				$SteamID = substr($SteamID, 0, $find);
			}

			return $SteamID;
		}

		// if a vanity url, then extract the name part and proceed to resolve it.
		$vanityName = substr($url, $find + 4);

		// if there's more URL left, remove it.
		$find = strpos($vanityName, "/");

		if ($find) {
			$vanityName = substr($vanityName, 0, $find);
		}

		return $this->ResolveVanityName($vanityName);
	}

	/**
	 * Given a profile vanity name, returns a SteamID, or false if there's an error.
	 * @param string $vanityName
	 * @return string|bool
	 */
	function ResolveVanityName($vanityName)
	{
		$result = json_decode(file_get_contents("https://api.steampowered.com/ISteamUser/ResolveVanityURL/v1/?key=" . $this->api_key . "&vanityurl=" . $vanityName));

		$response = $result->response;

		if ($response->success != 1) {
			return false;
		}

		return $response->steamid;
	}

	/**
	 * Given a URL to a steam profile, returns a 'SteamUser' object with user information, or 'false' if there's an error.
	 * @param string $url
	 * @return SteamUser|bool
	 */
	function GetSteamUserByURL($url) {
		$SteamID = $this->ResolveProfileURL($url);

		return $this->GetSteamUser($SteamID);
	}

	/**
	 * Given a SteamID, returns a 'SteamUser' object with user information, or 'false' if there's an error.
	 * @param string $SteamID
	 * @return SteamUser|bool
	 */
	function GetSteamUser($SteamID)
	{
		$result = json_decode(file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . $this->api_key . "&steamids=" . $SteamID));

		$response = $result->response;

		if (sizeof($response->players) != 1) {
			return false;
		}

		return new SteamUser($response->players[0]);
	}

	/**
	 * Given a URL to a steam application, returns a 'SteamApp' object with app information, or 'false' if there's an error.
	 * @param string $url
	 * @return SteamApp|bool
	 */
	function GetSteamAppByURL($url)
	{
		$find = strpos($url, "/app/");

		if (!$find) {
			return false;
		}

		$appid = substr($url, $find + 5);

		// if there's more URL left, remove it.
		$find = strpos($appid, "/");

		if ($find) {
			$appid = substr($appid, 0, $find);
		}

		return $this->GetSteamApp($appid);
	}

	/**
	 * Given an appid, returns a 'SteamApp' object with app information, or 'false' if there's an error.
	 * @param string|int $AppID
	 * @return SteamApp|bool
	 */
	function GetSteamApp($AppID)
	{
		$result = json_decode(file_get_contents("https://store.steampowered.com/api/appdetails?appids=" . $AppID));

		$response = $result->{$AppID};

		if ($response->success == false) {
			return false;
		}

		return new SteamApp($response->data);
	}

	/**
	 * Returns a 'CStrikeStatus' object with information about Counter-Strike's status.
	 * @return CStrikeStatus
	 */
	function GetCStrikeStatus()
	{
		$result = json_decode(file_get_contents("https://api.steampowered.com/ICSGOServers_730/GetGameServersStatus/v1/?key=" . $this->api_key));

		$response = $result->result;

		return new CStrikeStatus($response);
	}
}

class SteamUser
{
	/**
	 * Steam ID of the user.
	 * @var string
	 */
	public $steamid;
	/**
	 * Public name of the user
	 * @var string
	 */
	public $name;
	/**
	 * Real name of the user, if available.
	 * @var string|bool
	 */
	public $real_name = false;
	/**
	 * Profile URL for this user, if available.
	 * @var string|bool
	 */
	public $profile_url = false;
	/**
	 * Profile visibility value. DO NOT USE. Use GetProfileVisibility() instead.
	 * @var int|bool
	 */
	public $profile_visibility = false;
	/**
	 * URL to the user's avatar, if available.
	 * @var string|bool
	 */
	public $avatar_url = false;
	/**
	 * Hash of the user's avatar, if available.
	 * @var string|bool
	 */
	public $avatar_hash = false;
	/**
	 * UNIX timestamp of the user's last seen time, if available. Use GetLastSeen() for a formatted return.
	 * @var int|bool
	 */
	public $last_seen_unix = false;
	/**
	 * UNIX timestamp of the user's creation date, if available. Use GetCreationDate() for a formatted return.
	 * @var int|bool
	 */
	public $account_created_unix = false;
	/**
	 * Integer value of the user's status. Not recommended, use GetUserStatus() instead.
	 * @var int|bool
	 */
	public $previous_names = false;
	/**
	 * Numbered array containing previous names. Subkeys: 'newname' is a string of the name, 'timechanged' is the date and time it was set.
	 * @var array|bool
	 */
	public $status = false;
	/**
	 * Game the user is playing. Not recommended, use GetUserGame() instead.
	 * @var int|bool
	 */
	public $playing_game = false;
	public $server_ip = false;

	function __construct($userData)
	{
		$this->steamid = $userData->steamid;
		$this->name = $userData->personaname;
		$this->profile_visibility = $userData->communityvisibilitystate;

		if (isset($userData->avatarfull))
			$this->avatar_url = $userData->avatarfull;

		if (isset($userData->avatarhash))
			$this->avatar_hash = $userData->avatarhash;

		if (isset($userData->realname))
			$this->real_name = $userData->realname;

		if (isset($userData->personastate))
			$this->status = $userData->personastate;

		if (isset($userData->lastlogoff))
			$this->last_seen_unix = $userData->lastlogoff;

		if (isset($userData->timecreated))
			$this->account_created_unix = $userData->timecreated;

		if (isset($userData->gameextrainfo))
			$this->playing_game = $userData->gameextrainfo;

		if (isset($userData->gameserverip))
			$this->server_ip = $userData->gameserverip;

		if (isset($userData->profileurl))
			$this->profile_url = $userData->profileurl;

		if ($this->profile_url != false)
			$this->previous_names = json_decode(file_get_contents($this->profile_url . "/ajaxaliases/"), true);
	}

	/**
	 * Returns wether the user's profile is 'Public' or 'Private'.
	 * @return string
	 */
	function GetProfileVisibility()
	{
		if ($this->profile_visibility == 3) {
			return "Public";
		} else {
			return "Private";
		}
	}

	/**
	 * Returns the current status of the user.
	 * @return string
	 */
	function GetUserStatus()
	{
		switch ($this->status) {
			case 0:
				return "Offline (or private)";
			case 1:
				return "Online";
			case 2:
				return "Busy";
			case 3:
				return "Away";
			case 4:
				return "Snooze";
			case 5:
				return "Looking to trade";
			case 6:
				return "Looking to play";
			default:
				return "Unknown";
		}
	}

	/**
	 * Returns the name of the game being played by the user, if any.
	 * @return string
	 */
	function GetUserGame()
	{
		if ($this->playing_game) {
			return $this->playing_game;
		}

		return "Not playing";
	}

	/**
	 * Returns the IP of the server where the user is playing, if any. Only works for SteamWorks games.
	 * @return string
	 */
	function GetUserServerIP()
	{
		if ($this->server_ip) {
			return $this->server_ip;
		}

		return "Not playing any SteamWorks game match.";
	}

	/**
	 * Returns a text-formatted date of the last time this user was seen online.
	 * @return string
	 */
	function GetLastSeen()
	{
		if ($this->last_seen_unix) {
			return date("F j, Y, g:i a", $this->last_seen_unix);
		}

		return "Unknown";
	}

	/**
	 * Returns a text-formatted date of when this user's account was created.
	 * @return string
	 */
	function GetCreationDate()
	{
		if ($this->account_created_unix) {
			return date("F j, Y, g:i a", $this->account_created_unix);
		}

		return "Unknown";
	}
}

class SteamApp
{
	/**
	 * Application ID
	 * @var int
	 */
	public $appid;
	/**
	 * Application name
	 * @var string
	 */
	public $name;
	/**
	 * Is the game free to play or not
	 * @var bool
	 */
	public $free_to_play;
	/**
	 * How many players are playing right now. 0 if failed to fetch or game is unreleased.
	 * @var int
	 */
	public $playing_right_now;
	/**
	 * Level of controller support, from 'none' to 'full'
	 * @var string
	 */
	public $controller_support;
	/**
	 * A long, detailed description of the game.
	 * @var string
	 */
	public $detailed_description;
	/**
	 * A short description of the game.
	 * @var string
	 */
	public $short_description;
	/**
	 * A comma separated list of languages supported by the application.
	 * @var string
	 */
	public $language_list; 
	/**
	 * A numbered array containing the categories of this application. Subkeys: 'id' is an integer, 'description' is the name of the category in string form.
	 * @var array
	 */
	public $categories; 
	/**
	 * A numbered array containing the genres of this application. Subkeys: 'id' is an integer, 'description' is the name of the genre in string form.
	 * @var array
	 */
	public $genres; 
	/**
	 * Amount of achievements in the application, if any.
	 * @var int
	 */
	public $achievement_count; 
	/**
	 * A numbered array containing the name of the application developer/s
	 * @var array
	 */
	public $developers; 
	/**
	 *  A numbered array containing the name of the application publisher/s
	 * @var array
	 */
	public $publishers; 
	/**
	 * Is the game coming soon?
	 * @var bool
	 */
	public $coming_soon; 
	/**
	 * A release date for the application, might be past or future (check the 'coming_soon' value).
	* @var string
	*/
	public $release_date; 

	function __construct($appData)
	{
		$this->appid = $appData->steam_appid;
		$this->name = $appData->name;
		$this->free_to_play = $appData->is_free;
		$this->controller_support = $appData->controller_support;
		$this->detailed_description = $appData->detailed_description;
		$this->short_description = $appData->short_description;
		$this->language_list = $appData->supported_languages;
		$this->categories = json_decode(json_encode($appData->categories), true);
		$this->genres = json_decode(json_encode($appData->genres), true);
		$this->achievement_count = $appData->achievements->total;
		$this->developers = json_decode(json_encode($appData->developers), true);
		$this->publishers = json_decode(json_encode($appData->publishers), true);
		$this->coming_soon = $appData->release_date->coming_soon;
		$this->release_date = $appData->release_date->date;

		$fetch_current_players = json_decode(file_get_contents("https://api.steampowered.com/ISteamUserStats/GetNumberOfCurrentPlayers/v1/?appid=" . $this->appid));

		if ($fetch_current_players->response->result != 1) {
			$this->playing_right_now = 0;
		}
		else {
			$this->playing_right_now = $fetch_current_players->response->player_count;
		}
	}
}

class CStrikeStatus
{
	/**
	 * matchmaking status
	 * @var string
	 */
	public $mm_status;
	/**
	 * Players currently online in official CS.
	 * @var int
	 */
	public $online_players;
	/**
	 * Number of online CS servers.
	 * @var int
	 */
	public $online_servers;
	/**
	 * Number of players searching for game
	 * @var int
	 */
	public $searching_players;
	/**
	 * Average time, in seconds, to find a match
	 * @var int
	 */
	public $average_wait_seconds;
	/**
	 * Array containing load and capacity of every datacenter, keyed by location name.
	 * @var array
	 */
	public $datacenters;
	public $pworld_status;
	public $services;

	function __construct($data)
	{
		$this->mm_status = $data->matchmaking->scheduler;

		$this->online_players = $data->matchmaking->online_players;

		$this->online_servers = $data->matchmaking->online_servers;

		$this->searching_players = $data->matchmaking->searching_players;

		$this->average_wait_seconds = $data->matchmaking->search_seconds_avg;

		// this looks stupid, and it kinda is, but it quickly, properly and recursively converts stdClass into an array.
		$this->datacenters = json_decode(json_encode($data->datacenters), true);

		$this->pworld_status = $data->perfectworld->logon->availability;

		$this->services = $data->services;
	}

	/**
	 * Returns an array for the given datacenter name which includes 'capacity' and 'load'. Returns 'false' if the DC is not found.
	 * @param string $DCName
	 * @return array|bool
	 */
	function GetDatacenterStatus($DCName)
	{
		$check = array_key_exists($DCName, $this->datacenters);

		if (!$check)
			return false;

		return $this->datacenters[$DCName];
	}

	/**
	 * Return a text-formatted time, in minutes and seconds, of the average wait time to find a match.
	 * @return string
	 */
	function GetAverageWaitTime() {
		return date('i:s', $this->average_wait_seconds);
	}
}

?>