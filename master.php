<?php

require_once "./atomReader.php";
$config = require_once "./config.php";
$db = new PDO($config["dsn"], $config["user"], $config["password"]);

$sources = [];

/*
- Try these feeds:
	$sources = [
		[
			"feed" => "https://plausible.io/blog/feed.xml",
			"favicon" => "https://plausible.io/assets/images/icon/favicon.png",
			"site" => "https://plausible.io/"
		],
		[
			"feed" => "https://antyweb.pl/feed/atom",
			"favicon" => "https://antyweb.pl/wp-content/themes/antyweb.autentika/favicon/apple-touch-icon-152x152.png",
			"site" => "https://antyweb.pl/"
		],
		[
			"feed" => "https://blogs.windows.com/feed/atom/",
			"favicon" => "https://46c4ts1tskv22sdav81j9c69-wpengine.netdna-ssl.com/wp-content/uploads/2016/12/cropped-Windows-logo1-300x300.png",
			"site" => "https://blogs.windows.com/"
		],
		[
			"feed" => "https://www.omgubuntu.co.uk/feed/atom",
			"favicon" => "https://149366088.v2.pressablecdn.com/wp-content/themes/omgubuntu-theme-2021_0_1/images/favicons/favicon-180x180.png",
			"site" => "https://omgubuntu.co.uk/"
		],
		[
			"feed" => "https://protonmail.com/blog/feed/atom/",
			"favicon" => "https://protonmail.com/apple-touch-icon.png",
			"site" => "https://protonmail.com/"
		],
		[
			"feed" => "https://css-tricks.com/feed/atom/",
			"favicon" => "https://css-tricks.com/apple-touch-icon.png",
			"site" => "https://css-tricks.com/"
		],
		[
			"feed" => "https://hacks.mozilla.org/feed/atom/",
			"favicon" => "https://2r4s9p1yi1fa2jd7j43zph8r-wpengine.netdna-ssl.com/wp-content/themes/Hax/img/mdn-logo-mono.svg",
			"site" => "https://hacks.mozilla.org/"
		],
		[
			"feed" => "https://vercel.com/atom",
			"favicon" => "https://assets.vercel.com/image/upload/q_auto/front/favicon/vercel/180x180.png",
			"site" => "https://vercel.com/blog"
		],
		[
			"feed" => "https://android.com.pl/feed/atom/",
			"favicon" => "https://img.android.com.pl/images/user-images/2020/01/cropped-favion-192x192.png",
			"site" => "https://android.com.pl"
		],
		[
			"feed" => "https://arstechnica.com/feed/atom/",
			"favicon" => "https://cdn.arstechnica.net/wp-content/themes/ars/assets/img/ars-ios-icon-d9a45f558c.png",
			"site" => "https://arstechnica.com/"
		]
	];
*/

foreach ($sources as $key => $value) {
	$feed = new AtomReader($value["feed"]);
	foreach ($feed->articles() as $key2 => $value2) {
		$query = $db->prepare("SELECT *  FROM `already_sent` WHERE `atom_id` = :id");
		$id = strval($value2->id);
		$query->bindParam(':id', $id, PDO::PARAM_STR);
		$query->execute();
		$resp = $query->fetchAll(PDO::FETCH_OBJ);
		if ($resp == []) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "DISCORD WEBHOOK URL");
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json; charset=utf-8"]);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
				"content" => null,
				"embeds" => [
					[
						"title" => strval($value2->title[0]),
						"description" => strip_tags(strval($value2->summary[0])),
						"url" => strval($value2->href[0]) == "" ? strval($value2->link["href"]) : strval($value2->href[0]),
						"color" => 5814783,
						"author" => [
							"name" => strval($feed->title()),
							"url" => $value["site"],
							"icon_url" => $value["favicon"]
						],
						"footer" => [
							"text" => "pzpl News 1.0 | Article author: " . strval($value2->author->name)
						],
						"image" => [
							"url" => strval($value2->thumbnail["url"]) ?? null
						]
					]
				],
				"username" => "pzpl News",
				"avatar_url" => "https://cdn.discordapp.com/attachments/444866910035771415/845179766432333864/Feed-icon.png"
			]));
			$response = curl_exec($ch);
			$query = $db->prepare("INSERT INTO `already_sent` (`id`, `atom_id`) VALUES (NULL, :id);");
			$query->bindParam(':id', $id, PDO::PARAM_STR);
			$query->execute();
			sleep(1);
		}
	}
	sleep(3);
}
