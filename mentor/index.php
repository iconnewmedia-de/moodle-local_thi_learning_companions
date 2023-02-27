<?php

use local_learningcompanions\mentors;

require_once '../../../config.php';
require_once '../lib.php';

require_login();

$context = context_system::instance();
//require_capability( 'local/learningcompanions:mentor_view', $context);

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/learningcompanions/mentor/index.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_learningcompanions/mentor', 'init');
$PAGE->requires->css('/local/learningcompanions/js_lib/DataTables/datatables.min.css');
$PAGE->requires->css('/local/learningcompanions/js_lib/balloon.css');
$PAGE->navbar->add(get_string('navbar_mentors', 'local_learningcompanions'));
$PAGE->navbar->add(get_string('navbar_mentorquestions', 'local_learningcompanions'), new moodle_url('/local/learningcompanions/mentor/index.php'));

$mentortopics = mentors::get_all_mentor_keywords($USER->id, true);

$askedquestions = mentors::get_my_asked_questions($USER->id, true);
$mymentorquestions = mentors::get_mentor_questions_by_user_id($USER->id);
$allmentorquestions = mentors::get_mentor_questions_by_topics($mentortopics);
$learningNuggetComments = mentors::get_learning_nugget_comments();

echo $OUTPUT->header();

$notification = optional_param('n', null, PARAM_TEXT);
if (!is_null($notification)) {
    $notificationtype = substr($notification, 0, 2) === 'n_' ? 'error' : 'success';
    echo $OUTPUT->notification(get_string('notification_'.$notification, 'local_learningcompanions'), $notificationtype);
}

$hasaskedquestions = count($askedquestions) > 0;
$hasmentorquestions = count($mymentorquestions) > 0;
$hasallmentorquestions = count($allmentorquestions) > 0;
$haslearningnuggetcomments = count($learningNuggetComments) > 0;

echo $OUTPUT->render_from_template('local_learningcompanions/mentor/mentor_index', [
    'hasaskedquestions' => $hasaskedquestions,
    'hasmentorquestions' => $hasmentorquestions,
    'hasallmentorquestions' => $hasallmentorquestions,
    'haslearningnuggetcomments' => $haslearningnuggetcomments,
    'askedquestions' => array_values($askedquestions),
    'mymentorquestions' => array_values($mymentorquestions),
    'allmentorquestions' => array_values($allmentorquestions),
    'latestcomments' => array_values($learningNuggetComments),
    'ismentor' => mentors::is_mentor(),
    'istutor' => mentors::is_tutor(),
    'cfg' => $CFG
]);

echo $OUTPUT->footer();
