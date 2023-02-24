<?php

require_once dirname(__DIR__, 3) . '/config.php';

require_login();

global $USER;

$questionId = required_param('id', PARAM_INT);

$question = \local_learningcompanions\question::find($questionId);

if($question->get_askedby() !== (int)$USER->id && !is_siteadmin($USER->id)) {
    redirect(new moodle_url('/local/learningcompanions/mentor/', ), get_string('not_question_owner', 'local_learningcompanions'));
    die();
}

$question->mark_closed()->save();

redirect(new moodle_url('/local/learningcompanions/mentor/', ), get_string('question_closed', 'local_learningcompanions'));
