<?php

define('AJAX_SCRIPT', true);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

require_once dirname(__DIR__, 3). '/config.php';

require_login();

$groupId = required_param('groupId', PARAM_INT);
$lastPostId = required_param('lastPostId', PARAM_INT);

$chat = \local_learningcompanions\chat::createGroupChat($groupId);
$posts = $chat->get_newest_posts($lastPostId);

echo json_encode(['posts' => array_values($posts)]);
