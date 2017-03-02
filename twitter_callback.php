<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/19/17
 * Time: 8:04 PM
 */

session_start();

require_once __DIR__ . '/headers.php';
use Abraham\TwitterOAuth\TwitterOAuth;

$request_token = [];
$request_token['oauth_token'] = $_SESSION['oauth_token'];
$request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];

if (isset($_REQUEST['oauth_token']) && $request_token['oauth_token'] !== $_REQUEST['oauth_token']) {
    die("Some Error!");
}

$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $request_token['oauth_token'], $request_token['oauth_token_secret']);

$access_token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $_REQUEST['oauth_verifier']]);

$_SESSION['twitter_access_token'] = $access_token;

echo "<script>
    window.opener.location.reload();
    self.close();
</script>";