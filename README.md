# EZ-Steam-API-PHP
Easy steam api interface in PHP.

## Setup

Simply include the file into your script.

To make requests, you must create a SteamRequest object with your Steam API key.

```php
require 'ez_steam_api.php';
$steam = new SteamRequest("YOUR-API-KEY");
```

Grab your API key at http://steamcommunity.com/dev/apikey. This must be SECRET!

## Usage

### Requests
Requests are made through the SteamRequest object, declared as above.

```php
// resolve a user's profile URL into a SteamID
$SteamID = $steam->ResolveProfileURL("https://steamcommunity.com/id/Markski/");

// get a user's information
$userData = $steam->GetSteamUser($SteamID);

// get CS status
$csStatus = $steam->GetCStrikeStatus();
```

### Using information

Information is returned in the shape of Objects. For now these are SteamUser objects for User information, and CStrikeStatus objects for CS status information.

Most methods are documented through PHPDoc and you may just look at the suggestions when invocating these objects about what they can do. However, here's a quick summary:

#### SteamUser

The following information may be obtained raw from SteamUser:

- steamid
- name
- real_name
- profile_url
- profile_visibility
- avatar_url
- avatar_hash
- last_seen_unix
- account_created_unix
- status
- playing_game
- server_ip

For example:

```php
$userData = $steam->GetSteamUserByURL("https://steamcommunity.com/id/Markski/");

echo $userData->avatar_url;
// https://avatars.steamstatic.com/b7e10cbaaf0d6e428ee57a1c4bd91dee40681a72_full.jpg
```

The following methods return properly formatted data:

- GetProfileVisibility()
- GetUserStatus()
- GetUserGame()
- GetUserServerIP()
- GetLastSeen()
- GetCreationDate()

For example:

```php
$userData = $steam->GetSteamUserByURL("https://steamcommunity.com/id/Markski/");

echo $userData->GetCreationDate();
// March 18, 2012, 5:18 am

echo $userData->GetUserStatus();
// Online
```

It is recommended to use the formatted methods that exist instead of their raw counterparts.

For example, use `GetUserGame()` instead of getting `playing_game` directly, as it'll automatically handle returning 'Not playing' if the user is not playing any game.

Likewise, `status` will return an integer value, while `GetUserStatus()` will return a proper text value such as 'Online' or 'Busy'.

#### CStrikeStatus

The following information may be obtained raw from CStrikeStatus:

- mm_status (matchmaking status)
- online_players
- online_servers
- searching_players
- average_wait_seconds
- datacenters (an array of Datacenters, keyed by location name, which contain "capacity" and "load" statuses)
- pworld_status (perfect world, an array containing statuses for 'logon' and 'purchase')
- services (an array containing statuses for steam sessions and community)

For example:

```php
$csStatus = $steam->GetCStrikeStatus();

echo $csStatus->online_players;
// 516467

echo $csStatus->mm_status;
// "normal"
```

The following methods return properly formatted data:

- GetDatacenterStatus($DatacenterName)
- GetAverageWaitTime()

For example:

```php
$csStatus = $steam->GetCStrikeStatus();

echo $csStatus->GetAverageWaitTime();
// 01:35

$dcStatus = $csStatus->GetDatacenterStatus("Peru");

echo $dcStatus['load'];
// "medium"
```

## TODO

- Support for requesting Steam App information
- Want more? Let me know
