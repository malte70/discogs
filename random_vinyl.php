<?php
// Composer
require("vendor/autoload.php");
// Configuration
(require("config.inc.php")) or die("<!DOCTYPE html>\n<meta charset=\"UTF-8\">\n<h1>No configuration file found!</h1>\n<p>Please copy <tt>config.inc-dist.php</tt> to <tt>config.inc.php</tt> and change it to your needs.</p>");


/**
 * Throttle API calls
 *
 * @see https://github.com/calliostro/php-discogs-api#throttling
 */
$handler = \GuzzleHttp\HandlerStack::create();
$throttle = new Discogs\Subscriber\ThrottleSubscriber();
$handler->push(\GuzzleHttp\Middleware::retry($throttle->decider(), $throttle->delay()));


/**
 * Initialize Discogs API Client
 *
 * NOTE: Always set a user agent!
 */
$client = Discogs\ClientFactory::factory([
	'handler' => $handler,
	'headers' => [
		'User-Agent' => USER_AGENT,
		'Authorization' => "Discogs token=".DISCOGS_PERSONAL_ACCESS_TOKEN,
	],
]);


/**
 * Get collection items from Discogs API or cache file
 */
if (!file_exists(COLLECTION_ITEMS_CACHE_FILE) || filemtime(COLLECTION_ITEMS_CACHE_FILE) <= strtotime(CACHE_TIME)) {
	$keep_going = True;
	$releases = Array();
	$page = 1;
	while ($keep_going) {
		$items = $client->getCollectionItemsByFolder([
			'username' => DISCOGS_USER_NAME,
			'folder_id' => 0,
			'page' => $page,
			'per_page' => DISCOGS_PER_PAGE,
		]);
		$releases = array_merge($releases, $items["releases"]);
		if (count($items["releases"]) < 100) {
			$keep_going = False;
			break;
		}
		$page++;
	}
	
	file_put_contents(COLLECTION_ITEMS_CACHE_FILE, serialize($releases));

} else {
	$releases = unserialize(file_get_contents(COLLECTION_ITEMS_CACHE_FILE));

}


/**
 * Get releases from collection list and filter by format
 */
$releases_filtered = Array();
foreach ($releases as $item) {
	if ($item["basic_information"]["formats"][0]["name"] != DISCOGS_FORMAT_FILTER) {
		continue;
	}
	array_push($releases_filtered, $item);
}
$releases = $releases_filtered;
unset($releases_filtered);


/**
 * Get random collection item
 */
//$item_no = rand(0, count($releases)-1);
$item_no = random_int(0, count($releases)-1);
$item = $releases[$item_no];


/**
 * Create comma separated artist list
 */
$artists = "";
foreach ($item["basic_information"]["artists"] as $a) {
	$artists .= $a["name"] . ", ";
}
$artists = substr($artists, 0, -2);

/**
 * Record details displayed on web page
 */
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
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1.0">

		<title>Random <?=DISCOGS_FORMAT_FILTER?> from your collection</title>

		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css" integrity="sha512-NhSC1YmyruXifcj/KFRWoC561YpHpc5Jtzgvbuzx5VozKpWvQ+4nXhPdFgmx8xqexRcpAglTj9sIBWINXa8x5w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<link rel="stylesheet" href="style.css">
		<link rel="icon" type="image/png" href="<?=FAVICON_URL?>">
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
