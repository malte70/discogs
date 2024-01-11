<?php
require("vendor/autoload.php");
require("config.inc.php");


$handler = \GuzzleHttp\HandlerStack::create();
$throttle = new Discogs\Subscriber\ThrottleSubscriber();
$handler->push(\GuzzleHttp\Middleware::retry($throttle->decider(), $throttle->delay()));


$client = Discogs\ClientFactory::factory([
	'handler' => $handler,
	'headers' => [
		'User-Agent' => USER_AGENT,
		'Authorization' => "Discogs token=".DISCOGS_PERSONAL_ACCESS_TOKEN,
	],
]);


if (!file_exists(COLLECTION_ITEMS_CACHE_FILE) || filemtime(COLLECTION_ITEMS_CACHE_FILE) <= strtotime(CACHE_TIME)) {
	$items = $client->getCollectionItemsByFolder([
		'username' => DISCOGS_USER_NAME,
		'folder_id' => 0,
		'per_page' => DISCOGS_PER_PAGE,
	]);
	
	file_put_contents(COLLECTION_ITEMS_CACHE_FILE, serialize($items));

} else {
	$items = unserialize(file_get_contents(COLLECTION_ITEMS_CACHE_FILE));

}


$releases = Array();
foreach ($items["releases"] as $item) {
	if ($item["basic_information"]["formats"][0]["name"] != "Vinyl") {
		continue;
	}
	array_push($releases, $item);
}


$item_no = rand(0, count($releases)-1);
$item_no = random_int(0, count($releases)-1);
$item = $releases[$item_no];


$artists = "";
foreach ($item["basic_information"]["artists"] as $a) {
	$artists .= $a["name"] . ", ";
}
$artists = substr($artists, 0, -2);

$recordInfo = $artists . " - " . $item["basic_information"]["title"];
$recordDetails = ($item["basic_information"]["year"]!=0 ? $item["basic_information"]["year"] : "<i>Unknown year</i>") . "; " . implode(", ", $item["basic_information"]["formats"][0]["descriptions"]);


/**
 * Cover images
 */
$cover_image = $item["basic_information"]["cover_image"];
$img_local_filename = CACHE_DIR . "/" . hash("sha512", $cover_image) . ".jpg";
if (!file_exists($img_local_filename)) {
	file_put_contents($img_local_filename, $client->getHttpClient()->get($cover_image)->getBody()->getContents());
}


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
			main img {
				display: inline-block;
				width: 12.5em;
				margin: 3em 0 1em;
			}
			main h1 {
				font-size: 1.5em;
				font-weight: semi-bold;
				/*margin: 3em 0 1em;*/
				margin: 1em 0;
			}
			main p {
			}
		</style>
		<link rel="icon" type="image/png" href="https://dev.malte70.de/tango-icons/img/itunes-512.png">
	</head>
	<body id="top">
		<main>
			<img src="<?=$img_local_filename?>">
			<h1><?=$recordInfo?></h1>
			<p>
				<?=$recordDetails?>
			</p>
		</main>
	</body>
</html>
