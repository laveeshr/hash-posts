<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/26/17
 * Time: 8:03 PM
 */

require_once __DIR__ . '/vendor/autoload.php';

function getLongLivedFBToken($facebook, $accessToken){
    $oAuth2Client = $facebook->getOAuth2Client();
    $accessToken = new \Facebook\Authentication\AccessToken($accessToken);
    if (!$accessToken->isLongLived()) {
        // Exchanges a short-lived access token for a long-lived one
        try {
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken->getValue());
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
            exit;
        }
        return $accessToken->getValue();
    }
    return $accessToken->getValue();
}