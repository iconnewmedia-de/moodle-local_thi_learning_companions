<?php

require_once((dirname(__DIR__, 2)).'/config.php');

global $USER, $DB;

$groupid = required_param('groupid', PARAM_INT);

$errorCode = \local_learningcompanions\groups::join_group($USER->id, $groupid);

//if the group is closed, a request has been sent. If this is the case, redirect to the group search page
$group = $DB->get_record('lc_groups', ['id' => $groupid]);
if ($group && $group->closedgroup) {
    redirect(new moodle_url('/local/learningcompanions/group/search.php'), get_string('request_sent', 'local_learningcompanions'));
} else {
    redirect(new moodle_url('/local/learningcompanions/chat.php', ['groupid' => $groupid]));
}
