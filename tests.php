<?php
// test fetching, just replace your YOUR-API-KEY key and run.
require 'ez_steam_api.php';

$steam = new SteamRequest("YOUR-API-KEY");

$result = $steam->GetSteamUserByURL("https://steamcommunity.com/id/markski");

if ($result->steamid == "76561198060501071") {
    echo "SteamUser fetching correctly.<br/>";
}
else {
    exit("Error fetching SteamUser.");
}

$result = $steam->GetSteamAppByURL("https://steamcommunity.com/app/730");

if ($result->appid == 730) {
    echo "SteamApp fetching correctly.<br/>";
}
else {
    exit("Error fetching SteamApp.");
}

$result = $steam->GetCStrikeStatus();

if (sizeof($result->datacenters) > 0) {
    echo "CStrikeStatus fetching correctly.<br/>";
}
else {
    exit("Error fetching CStrikeStatus.");
}