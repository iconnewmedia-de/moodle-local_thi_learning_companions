<?php

define('AJAX_SCRIPT', true);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

require_once dirname(__DIR__, 3). '/config.php';
require_once __DIR__ . '/classes/chat.php';

require_login();

$groupId = required_param('groupId', PARAM_INT);
$lastPostId = required_param('lastPostId', PARAM_INT);

$chat = new local_learningcompanions\chat($groupId);
$posts = $chat->get_newest_posts($lastPostId);

echo json_encode(['posts' => array_values($posts)]);
