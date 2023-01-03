<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/classes/chat.php");
require_login();
$groupid = optional_param('groupid', 1, PARAM_INT); // ICTODO: turn this into a required param. Just using optional with default for testing/development purposes
$chatid = optional_param('chatid', 1, PARAM_INT); // ICTODO: turn this into a required param. Just using optional with default for testing/development purposes
$page = optional_param('page', 1, PARAM_INT);
$offset = optional_param('offset', 0, PARAM_INT);

$chat = new local_learningcompanions\chat($groupid);
$chat->set_latestviewedcomment($chatid);
$posts = $chat->get_comments($page, $offset);

$group = new \local_learningcompanions\group($groupid);

echo json_encode(["posts" => $posts ,"group" => $group]);
