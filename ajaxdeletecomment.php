<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/classes/chats.php");
require_login();
$commentid = required_param('commentid', PARAM_INT);
$success = \local_learningcompanions\chats::delete_comment($commentid);
echo json_encode(["success" => $success]);