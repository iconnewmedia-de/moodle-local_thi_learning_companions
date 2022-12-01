<?php
// AJAX script for reporting a chat message that is abusive/racist/sexist/whatever
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/classes/groups.php");
require_login();
global $USER;
$commentId = required_param('commentid', PARAM_INT);
require_once __DIR__ . "/classes/chats.php";
try {
    local_learningcompanions\chats::flag_comment($commentId);
    http_response_code(200);
    echo json_encode(['success' => true]);
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
