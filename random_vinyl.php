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


$items = $client->getCollectionItemsByFolder([
    'username' => 'malte70',
	'folder_id' => 0,
	'per_page' => 500,
]);


$releases = Array();
foreach ($items["releases"] as $item) {
	if ($item["basic_information"]["formats"][0]["name"] != "Vinyl") {
		continue;
	}
	array_push($releases, $item);
}


$item_no = rand(0, count($releases)-1);

/*print "Listing " . count($releases) . " Vinyl releases:\n\n";

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
}*/

$item = $releases[$item_no];

$artists = "";
foreach ($item["basic_information"]["artists"] as $a) {
	$artists .= $a["name"] . ", ";
}
$artists = substr($artists, 0, -2);

$recordInfo = $artists . " - " . $item["basic_information"]["title"];
$recordDetails = $item["basic_information"]["year"] . "; " . implode(", ", $item["basic_information"]["formats"][0]["descriptions"]);

?><!DOCTYPE html>
<html lang="de">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1.0">

		<title>Random Vinyl</title>

		<meta name="author" content="rolltreppe3">
		<link rel="stylesheet" href="https://xyz.rolltreppe3.de/css/normalize.css">
		<style>
			*, *:before, *:after { box-sizing: border-box; }
			body {
				font: 20px sans-serif;
				color: #333333;
				background: #e8e8e8;
			}
			main {
				text-align: center;
			}
			h1 {
				font-size: 1.5em;
				font-weight: semi-bold;
				margin: 3em 0 1em;
			}
		</style>
		<link rel="icon" type="image/png" href="https://dev.malte70.de/tango-icons/img/itunes-512.png">
	</head>
	<body id="top">
		<main>
			<h1><?=$recordInfo?></h1>
			<p>
				<?=$recordDetails?>
			</p>
		</main>
	</body>
</html>

