<?php

#index.php
session_start();

require_once __DIR__ . '/headers.php';
use Abraham\TwitterOAuth\TwitterOAuth;


//Helpers to get Access Tokens
try {
    $helper = $facebook->getRedirectLoginHelper();
    $fb_access_token = $helper->getAccessToken();
} catch(\Exception $ex) {
    echo $ex->getMessage();
}
if (!$fb_access_token) {
    // next try from canvas
    try {
        $helper = $facebook->getCanvasHelper();
        $fb_access_token = $helper->getAccessToken();
    } catch(\Exception $ex) {
        echo $ex->getMessage();
    }
}
if (!$fb_access_token) {
    // next try from JS
    try {
        $helper = $facebook->getRedirectLoginHelper();
        $fb_access_token = $helper->getAccessToken();
    } catch(\Exception $ex) {
        echo $ex->getMessage();
    }
}

if(!$fb_access_token && !isset($_SESSION['fb_access_token'])){
    $helper = $facebook->getRedirectLoginHelper();
    $permissions = ['email', 'user_likes', 'user_posts']; // optional
    $fb_login_url = $helper->getLoginUrl(FB_CALLBACK, $permissions);

//    echo '<a href="' . $fb_login_url . '">Log in with Facebook!</a>';
}
else
{
    $fb_access_token = !isset($fb_access_token) ? $_SESSION['fb_access_token'] : $fb_access_token;
    try{
        $response = $facebook->get('/me?fields=id,name', $fb_access_token);
    }
    catch (Facebook\Exceptions\FacebookSDKException $e){
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }

    $response = $response->getDecodedBody();
    $fbid = $response['id'];
    $fbname = $response['name'];
//    echo "FBName - ".$response['name'];

    $query = 'SELECT twitterid, twitter_auth_token, twitter_auth_secret FROM user_data WHERE fbid='.$fbid.';';
    $result = $conn->query($query);
    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()){
            $twitter_id = $row['twitterid'];
            $twitter_auth_token = $row['twitter_auth_token'];
            $twitter_auth_secret = $row['twitter_auth_secret'];
            break;  //Currently only supporting 1-1 relation, have provision for 1-many relation
        }
    }
}

//Twitter ID set
if(isset($twitter_id) || isset($_SESSION['twitter_access_token']))
{
    $twitter_auth_token = !isset($twitter_auth_token) ? $_SESSION['twitter_access_token']['oauth_token'] : $twitter_auth_token;
    $twitter_auth_secret = !isset($twitter_auth_secret) ? $_SESSION['twitter_access_token']['oauth_token_secret'] : $twitter_auth_secret;

    $twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $twitter_auth_token, $twitter_auth_secret);
    $user = $twitter->get("account/verify_credentials");
    $twitter_screen_name = $user->screen_name;
    $twitter_id = !isset($twitter_id) ? $user->id : $twitter_id;
//    echo "<br>Twitter Handle - ".$twitter_screen_name;
}
elseif(!isset($twitter_id) && !isset($_SESSION['twitter_access_token']))
{
    $twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
    $request_token = $twitter->oauth('oauth/request_token', array('oauth_callback' => TWITTER_OAUTH_CALLBACK));

    $_SESSION['oauth_token'] = $request_token['oauth_token'];
    $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

    $twitter_login_url = $twitter->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

//    echo '<br><a href="#" onclick="window.open(\''.$twitter_login_url.'\', name=\'twitterLogin\', \'width=1000,height=1000,scrollbars=yes\')">Log in with Twitter!</a>';
}

if(isset($fb_access_token) && isset($_SESSION['twitter_access_token'])) //This will only update on the case of both logins
{
    $fb_access_token = getLongLivedFBToken($facebook, $fb_access_token);
    $query = 'INSERT INTO user_data VALUES('.$fbid.','.$twitter_id.',"'.$fb_access_token.'",'.time().',"'.
        $_SESSION['twitter_access_token']['oauth_token'].'","'.$_SESSION['twitter_access_token']['oauth_token_secret'].'", '.time().')
         ON DUPLICATE KEY UPDATE fbid='.$fbid.';';
//    echo $query;
    if(!$conn->query($query)){
        exit("Insertion error: ". $conn->error());
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>#Posts</title>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    <link rel='stylesheet' type='text/css' href='//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css'>
</head>
<body>
<div class="jumbotron" style="">
    <h1 style="text-align: center">Welcome to <span class="label label-primary">#Posts</span></h1>
    <p>
        Welcome to #Posts. We help you post anything on Twitter directly from your FB page! Just add '#' before any words that you want to tweet! It's that simple!
    </p>
    <p>
        To start using it just authorize both your fb account and your twitter account and you're good to go.
    </p>
    <div class="row" style="text-align: center;">
        <div class="col-lg-3">
            <?php
                if(!isset($fbname)){
                    echo '<a href="'.$fb_login_url.'"><button type="button" class="btn btn-primary">Sign in with FB</button></a>';
                }
                else{
                    echo '<p class="well">Logged In as : <span class="label label-primary">'.$fbname.'</span></p>';
                }
            ?>
        </div>
        <div class="col-lg-3">
            <?php
                if(isset($twitter_screen_name)){
                    echo '<p class="well">Twitter Login : <span class="label label-info">'.$twitter_screen_name.'</span></p>';
                }
                else{
                    echo '<a href="#" onclick="window.open(\''.$twitter_login_url.'\', name=\'twitterLogin\', \'width=1000,height=1000,scrollbars=yes\')"><button type="button" class="btn btn-info">Sign in with Twitter</button></a>';
                }
            ?>
        </div>
        <div class="">
            <?php
                if(isset($twitter_id) && isset($fbid))
                {
                    $del_url = 'delete_user.php?fbid='.$fbid.'&twitterid='.$twitter_id;
                    echo '<a href="'.$del_url.'"><button type="button" class="btn btn-danger">Delete User</button></a>';
                }
            ?>
        </div>
    </div>
</div>
</body>
</html>
