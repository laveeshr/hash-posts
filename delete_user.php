<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/26/17
 * Time: 10:03 PM
 */

session_start();

require_once __DIR__ . '/headers.php';

$fbid = $_REQUEST['fbid'];
$twitter_id = $_REQUEST['twitterid'];

$del_query = "DELETE FROM user_data WHERE fbid=".$fbid." AND twitterid=".$twitter_id.";";
if(!$conn->query($del_query)){
    exit("Deletion error: ". $conn->error());
}
session_destroy();
header("Location: index.php");