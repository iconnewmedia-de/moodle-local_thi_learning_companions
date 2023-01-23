<?php

require_once((dirname(__DIR__, 2)).'/config.php');

global $USER;

$groupid = required_param('groupid', PARAM_INT);

$joined = \local_learningcompanions\groups::join_group($USER->id, $groupid);

redirect(new moodle_url('/local/learningcompanions/chat.php', ['groupid' => $groupid]));
