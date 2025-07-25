<?php

require_once("config.inc.php");



?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<title>malte70/discogs</title>
		
		<link rel="icon" type="image/png" href="<?=FAVICON_URL?>">
		<link rel="stylesheet" type="text/css" href="file-list.css">
	</head>
	<body>
		<header>
			<h1>
				<a href="./">malte70/discogs</a><br>
				<small>Get a random item in Vinyl format from your Discogs collection</small>
			</h1>
		</header>
		
		<main>
			<ul>
				<li><a href="https://github.com/malte70/discogs"> &rarr; Github-Repo</a></li>
				<li class="separator">&nbsp;</li>
				<li><a href="list.php"><em>list.php</em>: List all vinyl records in the collection</a></li>
				<li><a href="random_vinyl.php"><em>random_vinyl.php</em>: Show a random vinyl record (including cover image)</a></li>
			</ul>
		</main>
	</body>
</html>
