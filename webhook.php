  <?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/13/17
 * Time: 2:51 PM
 */

require_once 'headers.php';

//  Check if get request for setting up webhook
    if(array_key_exists('hub_challenge', $_REQUEST))
    {
        $challenge = $_REQUEST['hub_challenge'];
        $verify_token = $_REQUEST['hub_verify_token'];

        if($verify_token == "abc123"){
            echo $challenge;
            exit();
        }
    }
    //Webhook subscription
    else
    {
        $input = json_decode(file_get_contents('php://input'), true);

        foreach ($input['entry'] as $entry)
        {
          $fbid =$entry['uid'];

          //Get All Access Tokens
          $query = 'SELECT fb_access_token, fb_last_update, twitter_auth_token, twitter_auth_secret, fb_token_last_update FROM user_data WHERE fbid='.$fbid.';';
          $result = $conn->query($query);
          if($result->num_rows > 0)
          {
              while($row = $result->fetch_assoc())
              {
                  $fb_access_token = $row['fb_access_token'];
                  $fb_last_updated = $row['fb_last_update'];
                  $twitter_auth_token = $row['twitter_auth_token'];
                  $twitter_auth_secret = $row['twitter_auth_secret'];
                  $fb_token_last_update = $row['fb_token_last_update'];
                  break;
              }
          }
          else
          {
              continue;
          }
          $response = $facebook->get('/'.$fbid.'?fields=posts.since('.$fb_last_updated.')&date_format=U', $fb_access_token);
          /* handle the result */
          $response = $response->getDecodedBody();

          if(!array_key_exists('posts', $response))
          {
              continue;
          }

          $twitter = new \Abraham\TwitterOAuth\TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $twitter_auth_token, $twitter_auth_secret);
          foreach ($response['posts']['data'] as $data)
          {
              if(array_key_exists('message', $data))
              {
                  $msg = $data['message'];
                  $tweets = array();
                  preg_match_all('/#([\\p{L}\\p{Mn}]+[0-9_]*)+/u', $msg, $tweets);
                  if(count($tweets) < 1)
                  {
                      continue;
                  }
//                  print_r($tweets);
//                  $msg = ;
                  $fb_last_updated = $fb_last_updated > $data['created_time'] ? $fb_last_updated : $data['created_time'];
                  $statuses = $twitter->post("statuses/update", ["status" => implode(" ", $tweets[0])]);
              }
          }

          $next_week = strtotime("+1 week", $fb_token_last_update);
          if(time() >= $next_week)
          {
              //    Generate new fb access token
                $oAuth2Client = $facebook->getOAuth2Client();
                $code = $oAuth2Client->getCodeFromLongLivedAccessToken($access_token);
                $new_access_token = $oAuth2Client->getAccessTokenFromCode($code);

                $update_query = "UPDATE user_data SET fb_access_token='".$new_access_token."', fb_last_update=".$fb_last_updated.", fb_token_last_update=".time()." WHERE fbid=".$fbid.";";
          }
          else
          {
              //Update last FB accessed
              $update_query = "UPDATE user_data SET fb_last_update=".$fb_last_updated." WHERE fbid=".$fbid.";";
          }
          $conn->query($update_query);
        }
        $conn->close();
    }
