<?php

class SteamRequest
{
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
	 * Given a SteamID, returns a 'SteamUser' object with user information, or 'false' if there's an error.
	 * @param mixed $SteamID
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

		return $this->GetSteamApp($appid);
	}

	/**
	 * Given an appid, returns a 'SteamApp' object with app information, or 'false' if there's an error.
	 * @param string|int $AppID
	 * @return SteamApp|bool
	 */
	function GetSteamApp($AppID)
	{
		throw new Exception('GetSteamApp is not yet implemented.');

		$result = json_decode(file_get_contents("https://api.steampowered.com/ISteamUserStats/GetNumberOfCurrentPlayers/v1/?appid=" . $AppID));

		$response = $result->response;

		if ($response->result != 1) {
			return false;
		}

		return new SteamApp($response);
	}

	/**
	 * Returns a 'CStrikeStatus' object with information about Counter-Strike's status.
	 * @return CStrikeStatus
	 */
	function GetCSGOStatus()
	{
		$result = json_decode(file_get_contents("https://api.steampowered.com/ICSGOServers_730/GetGameServersStatus/v1/?key=" . $this->api_key));

		$response = $result->result;

		return new CStrikeStatus($response);
	}
}

class SteamUser
{
	public $steamid;
	public $name;
	public $profile_url = false;
	public $real_name = false;
	public $profile_visibility = false;
	public $avatar_url = false;
	public $avatar_hash = false;
	public $last_seen_unix = false;
	public $account_created_unix = false;
	public $status = false;
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
	public $name;
	public $price_usd;
	public $playing_right_now;

	function __construct($json)
	{
		// todo
	}
}

class CStrikeStatus
{
	public $mm_status;
	public $online_players;
	public $online_servers;
	public $searching_players;
	public $average_wait_seconds;
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
	 * @param mixed $DCName
	 * @return array|bool
	 */
	function GetDatacenterStatus($DCName)
	{
		$check = array_key_exists($DCName, $this->datacenters);

		if (!$check)
			return false;

		return $this->datacenters[$DCName];
	}
}

?>