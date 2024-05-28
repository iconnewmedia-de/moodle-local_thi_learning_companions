<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
define('AJAX_SCRIPT', true);
require_once(dirname(__DIR__, 3). '/config.php');

require_login();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

global $USER, $PAGE, $DB;
$PAGE->set_context(context_system::instance());

$groupid = required_param('groupid', PARAM_INT);
$questionid = optional_param('questionid', 0, PARAM_INT);
$chatid = optional_param('chatid', 0, PARAM_INT);
if ($chatid === 0) {
    $chatid = $DB->get_field('thi_lc_chat', 'id',
        ['relatedid' => $groupid, 'chattype' => local_thi_learning_companions\groups::CHATTYPE_GROUP]
    );
}
$firstpostid = optional_param('firstPostId', null, PARAM_INT);
$includedpostid = optional_param('includedPostId', 0, PARAM_INT);
if ($questionid > 0) {
    $chat = \local_thi_learning_companions\chat::create_question_chat($questionid);
} else {
    $chat = \local_thi_learning_companions\chat::create_group_chat($groupid);
    // Check if the user is allowed to see the group.
    $group = new \local_thi_learning_companions\group($groupid);
    $context = context_system::instance();
    $canseeallgroups = has_capability( 'tool/thi_learning_companions:group_manage', $context);
    if (!$group->is_user_member($USER->id) && !$canseeallgroups) {
        $group->userIsNotAMember = true;
        // If the user is not an member, but the group is public, then allow them to see the group.
        if (!$group->closedgroup) {
            $group->isPreviewGroup = true;
        } else {
            $group = null;
            $other['viewNotAllowed'] = true;
            $posts = [];
        }
    }
}
$chat->set_latestviewedcomment($chatid);
$posts = $chat->get_posts_for_chat($firstpostid, $includedpostid);

$other = [];

echo json_encode(["posts" => array_values($posts) , "other" => $other]);
