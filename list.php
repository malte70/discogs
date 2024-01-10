<?php
require("vendor/autoload.php");
require("config.inc.php");


$handler = \GuzzleHttp\HandlerStack::create();
$throttle = new Discogs\Subscriber\ThrottleSubscriber();
$handler->push(\GuzzleHttp\Middleware::retry($throttle->decider(), $throttle->delay()));


$client = Discogs\ClientFactory::factory([
	'handler' => $handler,
	'headers' => [
		'User-Agent' => 'malte70-discogs/0.1 +https://malte70.de',
		'Authorization' => "Discogs token=".DISCOGS_PERSONAL_ACCESS_TOKEN,
	],
]);


header("Content-Type: text/plain; charset=UTF-8");


//$folders = $client->getCollectionFolders([
//    'username' => 'malte70'
//]);
//print_r($folders);

$items = $client->getCollectionItemsByFolder([
    'username' => 'malte70',
	'folder_id' => 0,
	'per_page' => 500,
]);

/*
print "\$items[releases](".count($items["releases"])."):\n";
print_r($items["releases"]);
*/


//print "Listing " . count($items["releases"]) . " releases:\n\n";
$releases = Array();
foreach ($items["releases"] as $item) {
	if ($item["basic_information"]["formats"][0]["name"] != "Vinyl") {
		continue;
	}
	array_push($releases, $item);
}

print "Listing " . count($releases) . " Vinyl releases:\n\n";

foreach ($releases as $k => $item) {
	$artists = "";
	foreach ($item["basic_information"]["artists"] as $a) {
		$artists .= $a["name"] . ", ";
	}
	$artists = substr($artists, 0, -2);

	print $artists . " - " . $item["basic_information"]["title"];
	//print " (" . $item["basic_information"]["year"] . ")";
	//print " (" . $item["basic_information"]["year"] . ", " . $item["basic_information"]["formats"][0]["name"] . ")";
	print " (" . $item["basic_information"]["year"] . ")";
	print "(" . $item["basic_information"]["formats"][0]["name"] . ", " . implode(", ", $item["basic_information"]["formats"][0]["descriptions"]) . ")";
	print "\n";
}

