<?php

define('AJAX_SCRIPT', true);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
require_once dirname(__DIR__, 3). '/config.php';

require_login();
global $USER, $PAGE;

$PAGE->set_context(context_system::instance());

$previewGroup = optional_param('shouldIncludeId', null, PARAM_INT);

$groups = local_thi_learning_companions\groups::get_groups_of_user($USER->id, $previewGroup);
foreach($groups as $group) {
    $group->comments_since_last_visit = \local_thi_learning_companions\groups::count_comments_since_last_visit($group->id);
    $group->has_new_comments = $group->comments_since_last_visit > 0;
    $lastcomment = strip_tags($group->get_last_comment());
    $group->lastcomment = $lastcomment;
    if (strlen($lastcomment) > 100) {
        $group->lastcomment = substr($lastcomment, 0, 97).'...';
    }
}
header('Content-Type: application/json');
$response = json_encode(["groups" => $groups]);
echo $response;
