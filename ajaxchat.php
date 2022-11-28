<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/classes/chat.php");
require_login();

$chat = new local_learningcompanions\chat(1);
$posts = $chat->get_comments();
echo json_encode(["posts" => $posts]);