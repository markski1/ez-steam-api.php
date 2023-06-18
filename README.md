# ez-steam-api.php
Simple, easy-to-use Steam API interface in PHP. 

***This is still a work in progress. There is some jankiness that will be getting worked out.***

## Setup

Simply include the file into your script.

You must create a SteamRequest object with your Steam API key as parameter.
All requests will be made through this object.

```php
require 'ez_steam_api.php';
$steam = new SteamRequest("YOUR-API-KEY");
```

Grab your API key at http://steamcommunity.com/dev/apikey. This must be SECRET!

## Usage

### Requests
Requests are made through the SteamRequest object, as declared above.

```php
// resolve a user's profile URL into a SteamID
$SteamID = $steam->ResolveProfileURL("https://steamcommunity.com/id/Markski/");

// get a user's information
$userData = $steam->GetSteamUser($SteamID);

// get an application's information
$appData = $steam->GetSteamAppByURL("https://steamcommunity.com/app/730/");

// get CS status
$csStatus = $steam->GetCStrikeStatus();
```

### Using information

When you make a request, you will obtain an object with the result. For now these are SteamUser for User information, SteamApp for application information, and CStrikeStatus for CS status information.

Please refer to the 'Important notes' section at the end of this document before using this in production.

Most methods and values are documented through PHPDoc and you may just look at the suggestions when using them to know what they contain and do. However, here's a quick summary:

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
- previous_names
- playing_game
- server_ip

For example:

```php
$userData = $steam->GetSteamUserByURL("https://steamcommunity.com/id/Markski/");

echo $userData->avatar_url;
// "https://avatars.steamstatic.com/b7e10cbaaf0d6e428ee57a1c4bd91dee40681a72_full.jpg"
```

The following getters return properly formatted data:

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
// "March 18, 2012, 5:18 am"

echo $userData->GetUserStatus();
// "Online"
```

#### SteamApp

The following information may be obtained raw from SteamUser:

- appid
- name
- free_to_play
- playing_right_now
- controller_support
- detailed_description
- short_description
- language_list
- categories
- genres
- achievement_count
- developers
- publishers
- coming_soon
- release_date

```php
$appData = $steam->GetSteamAppByURL("https://steamcommunity.com/app/730");

echo $appData->name;
// "Counter-Strike: Global Offensive"

echo $appData->playing_right_now;
// 1055883

echo $appData->free_to_play;
// true
```

For now, SteamApp does not offer any formatted getters. While I plan to improve on this, the raw values are fairly usable as they are.

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
// "01:35"

$dcStatus = $csStatus->GetDatacenterStatus("Peru");

echo $dcStatus['load'];
// "medium"
```


#### Important notes.

It is recommended to use the formatted getters that exist instead of their raw counterparts. Raw values will usually be in the shape of an arbitrary number or might be invalid in a way your script cannot handle. The methods provided take care of converting data into a usable shape, or returning `false` in case of failure. 

For example, in the case of SteamUser objects, use `GetUserGame()` instead of getting `playing_game` directly, as it'll automatically handle returning 'Not playing' if the user is not playing any game. Likewise, `status` will return an integer value, while `GetUserStatus()` will return a proper text value such as 'Online' or 'Busy'.

Also: The information within objects returned by this interface is fetched and cached at the instant the request is made. You cannot indefinitely use the same object to get up-to-date information.

## TODO

- Better methods and organization for SteamApp data
- Want more? Let me know
