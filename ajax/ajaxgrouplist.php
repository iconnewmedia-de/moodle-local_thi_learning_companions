<?php

define('AJAX_SCRIPT', true);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
require_once dirname(__DIR__, 3). '/config.php';
require_once(__DIR__ . "/classes/groups.php");
require_login();
global $USER;

$previewGroup = optional_param('shouldIncludeId', null, PARAM_INT);

$groups = local_learningcompanions\groups::get_groups_of_user($USER->id, $previewGroup);
header('Content-Type: application/json');
$response = json_encode(["groups" => $groups]);
echo $response;
