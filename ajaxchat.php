<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/classes/chat.php");
require_login();
$chatid = optional_param('chatid', 1, PARAM_INT); // ICTODO: turn this into a required param. Just using optional with default for testing/development purposes
$chat = new local_learningcompanions\chat(1);
$posts = $chat->get_comments();
$chat->set_latestviewedcomment($chatid);
echo json_encode(["posts" => $posts]);