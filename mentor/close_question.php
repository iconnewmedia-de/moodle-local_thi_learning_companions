<?php

require_once dirname(__DIR__, 3) . '/config.php';

require_login();

global $USER;

$questionId = required_param('id', PARAM_INT);

$question = \local_thi_learning_companions\question::find($questionId);

if($question->get_askedby() !== (int)$USER->id && !is_siteadmin($USER->id)) {
    redirect(new moodle_url('/local/thi_learning_companions/mentor/', ), get_string('not_question_owner', 'local_thi_learning_companions'));
    die();
}

$question->mark_closed()->save();

redirect(new moodle_url('/local/thi_learning_companions/mentor/', ), get_string('question_closed', 'local_thi_learning_companions'));
