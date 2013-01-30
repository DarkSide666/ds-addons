<?php
require("RollingCurl.php");

// top 20 sites according to alexa (11/5/09)
$urls = array(
              "http://www.facebook.com",
              "http://www.google.com",
/*              "http://www.yahoo.com",
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
              "http://www.wordpress.com",
              "http://www.sina.com.cn",
              "http://www.google.co.uk"
*/
);

// a little example that fetches a bunch of sites in parallel and echos the page title and response info for each request
function request_callback($response, $info, $request) {
    echo $request->data['id'].": finished<br />";
}



// ----------------------------------------------------------------------------
$start = microtime(true);

for($i=0;$i<count($urls);$i++) {
    echo $i.': '.$urls[$i].'<br />';
    // single curl request
    $rc = new RollingCurl("request_callback");
    $rc->get($urls[$i],null,null,array('id'=>$i));
    $rc->execute();
}

$finish = microtime(true);
echo '<hr />Single CURL finished in '.($finish-$start).' ms<hr />';



// ----------------------------------------------------------------------------
$start = microtime(true);

$rc = new RollingCurl("request_callback");
$rc->window_size = 20;

for($i=0;$i<count($urls);$i++) {
    echo $i.': '.$urls[$i].'<br />';
    // add to multi curl
    $rc->get($urls[$i],null,null,array('id'=>$i));
}
// execute multi curl requests
$rc->execute();

$finish = microtime(true);
echo '<hr />Multi CURL finished in '.($finish-$start).' ms<hr />';


