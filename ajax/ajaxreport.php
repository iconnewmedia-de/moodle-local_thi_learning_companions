<?php

define('AJAX_SCRIPT', true);

// AJAX script for reporting a chat message that is abusive/racist/sexist/whatever
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
require_once dirname(__DIR__, 3). '/config.php';

require_login();
global $USER;
$commentId = required_param('commentid', PARAM_INT);

try {
    local_thi_learning_companions\chats::flag_comment($commentId);
    http_response_code(200);
    echo json_encode(['success' => true]);
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
