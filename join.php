<?php

require_once((dirname(__DIR__, 2)).'/config.php');

global $USER, $DB;

$groupid = required_param('groupid', PARAM_INT);

$errorCode = \local_thi_learning_companions\groups::join_group($USER->id, $groupid);

//if the group is closed, a request has been sent. If this is the case, redirect to the group search page
$group = $DB->get_record('thi_lc_groups', ['id' => $groupid]);
if ($group && $group->closedgroup) {
    redirect(new moodle_url('/local/thi_learning_companions/group/search.php'), get_string('request_sent', 'local_thi_learning_companions'));
} else {
    redirect(new moodle_url('/local/thi_learning_companions/chat.php', ['groupid' => $groupid]));
}
