<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

    require_once __DIR__ . '/functions.php';

    $facebook = new Facebook\Facebook([
        'app_id' => '', //Your App ID
        'app_secret' => '', //Your App Secret
        'default_graph_version' => 'v2.8',
    ]);

    define('CONSUMER_KEY', '');    //Twitter Consumer KEY
    define('CONSUMER_SECRET', ''); //Twitter Consumer Secret
    define('TWITTER_OAUTH_CALLBACK', '');    //Twitter callback URL
    define('FB_CALLBACK', ''); //FB Callback URL

    try {
        $conn = new mysqli('localhost', 'root', '', 'selective_posts'); //MySQL Details
    }
    catch (Exception $e){
        echo "Error in connection: ".$e->getMessage();
    }
    if($conn->connect_error){
        die("Connection Failed". $conn->connect_error);
    }