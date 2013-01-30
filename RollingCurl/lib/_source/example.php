<?php
/*
authored by Josh Fraser (www.joshfraser.com)
released under Apache License 2.0

Maintained by Alexander Makarov, http://rmcreative.ru/

$Id$
*/

require("RollingCurl.php");

// a little example that fetches a bunch of sites in parallel and echos the page title and response info for each request
function request_callback($response, $info, $request) {
	// parse the page title out of the returned HTML
	if (preg_match("~<title>(.*?)</title>~i", $response, $out)) {
		$title = $out[1];
	} else {
        $title = "NO TITLE";
	}
	echo "<b>{$request->url}: $title</b>";
	//echo "<textarea cols=80 ROWS=10>".htmlspecialchars($response)."</textarea>";
	echo "<pre>";
	print_r($info);
    //print_r($request);
	echo "</pre><hr>";
}

/*
// single curl request
$rc = new RollingCurl("request_callback");
$rc->request("http://www.msn.com");
$rc->execute();

// another single curl request
$rc = new RollingCurl("request_callback");
$rc->request("http://www.google.com");
$rc->execute();

echo "<hr>";
*/

// top 20 sites according to alexa (11/5/09)
$urls = array("http://www.google.com",
              "http://www.facebook.com",
              "http://www.yahoo.com",
              "http://www.youtube.com",
              "http://www.live.com",
              "http://www.wikipedia.com",
              "http://www.blogger.com",
              "http://www.msn.com",
              "http://www.baidu.com",
              "http://www.yahoo.co.jp",
              "http://www.myspace.com",
              "http://www.qq.com",
              "http://www.google.co.in",
              "http://www.twitter.com",
              "http://www.google.de",
              "http://www.microsoft.com",
              "http://www.google.cn",
              "http://www.sina.com.cn",
              "http://www.wordpress.com",
              "http://www.google.co.uk");

$rc = new RollingCurl("request_callback");
$rc->window_size = 3;
$i=0;
foreach ($urls as $url) {
    $request = new RollingCurlRequest($url);
    $rc->add($request);
    $i++;
}
$rc->execute();
