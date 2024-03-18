<?php

define('AJAX_SCRIPT', true);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
require_once dirname(__DIR__, 3). '/config.php';

require_login();
global $USER, $PAGE;
$PAGE->set_context(context_system::instance());

$groupid = optional_param('groupid', 1, PARAM_INT); // ICTODO: turn this into a required param. Just using optional with default for testing/development purposes
$questionid = optional_param('questionid', 0, PARAM_INT); // ICTODO: turn this into a required param. Just using optional with default for testing/development purposes
$chatid = optional_param('chatid', 1, PARAM_INT); // ICTODO: turn this into a required param. Just using optional with default for testing/development purposes
$firstPostId = optional_param('firstPostId', null, PARAM_INT);
$includedPostId = optional_param('includedPostId', 0, PARAM_INT);
if ($questionid > 0) {
    $chat = \local_thi_learning_companions\chat::createQuestionChat($questionid);
} else {
    $chat = \local_thi_learning_companions\chat::createGroupChat($groupid);
    //Check if the user is allowed to see the group
    $group = new \local_thi_learning_companions\group($groupid);
    $context = context_system::instance();
    $canSeeAllGroups = has_capability( 'tool/thi_learning_companions:group_manage', $context);
    if (!$group->is_user_member($USER->id) && !$canSeeAllGroups) {
        $group->userIsNotAMember = true;
        //If the user is not an member, but the group is public, then allow them to see the group
        if(!$group->closedgroup) {
            $group->isPreviewGroup = true;
        } else {
            $group = null;
            $other['viewNotAllowed'] = true;
            $posts = [];
        }
    }
}
$chat->set_latestviewedcomment($chatid);
$posts = $chat->get_posts_for_chat($firstPostId, $includedPostId);

$other = [];

echo json_encode(["posts" => array_values($posts) , "other" => $other]);
