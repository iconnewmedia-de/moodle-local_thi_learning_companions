<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/classes/chat.php");
require_login();
global $USER;

$groupid = optional_param('groupid', 1, PARAM_INT); // ICTODO: turn this into a required param. Just using optional with default for testing/development purposes
$chatid = optional_param('chatid', 1, PARAM_INT); // ICTODO: turn this into a required param. Just using optional with default for testing/development purposes
$firstPostId = optional_param('firstPostId', null, PARAM_INT);
$includedPostId = optional_param('includedPostId', 0, PARAM_INT);
//$previewGroup = optional_param('previewGroup', null, PARAM_INT);

$chat = new local_learningcompanions\chat($groupid);
$chat->set_latestviewedcomment($chatid);
$posts = $chat->get_posts_for_chat($firstPostId, $includedPostId);

$group = new \local_learningcompanions\group($groupid);

$context = context_system::instance();
$canSeeAllGroups = has_capability( 'tool/learningcompanions:group_manage', $context);

$other = [];

//Check if the user is allowed to see the group
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

echo json_encode(["posts" => array_values($posts) , "other" => $other]);
