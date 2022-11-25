<?php
require_once '../../../config.php';
require_once '../lib.php';

$context = context_system::instance();
require_capability( 'local/learningcompanions:mentor_view', $context);

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/learningcompanions/mentor/index.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_learningcompanions/mentor', 'init');
$PAGE->requires->css('/local/learningcompanions/vendor/DataTables/datatables.min.css');
$PAGE->requires->css('/local/learningcompanions/vendor/balloon.css');
$PAGE->navbar->add(get_string('navbar_mentors', 'local_learningcompanions'));
$PAGE->navbar->add(get_string('navbar_mentorquestions', 'local_learningcompanions'), new moodle_url('/local/learningcompanions/mentor/index.php'));

$mentortopics = \local_learningcompanions\mentors::get_all_mentor_keywords($USER->id, true);

$askedquestions = \local_learningcompanions\mentors::get_my_asked_questions($USER->id, true);
$mymentorquestions = \local_learningcompanions\mentors::get_all_mentor_questions($USER->id, null, false, true);
$allmentorquestions = \local_learningcompanions\mentors::get_all_mentor_questions(null, $mentortopics, false, true);
$learningnuggetcomments = \local_learningcompanions\mentors::get_latest_nugget_comments($USER->id); // ICTODO

echo $OUTPUT->header();

$notification = optional_param('n', null, PARAM_TEXT);
if (!is_null($notification)) {
    $notificationtype = substr($notification, 0, 2) == 'n_' ? 'error' : 'success';
    echo $OUTPUT->notification(get_string('notification_'.$notification, 'local_learningcompanions'), $notificationtype);
}

$hasaskedquestions = count($askedquestions) > 0;
$hasmentorquestions = count($mymentorquestions) > 0;
$hasallmentorquestions = count($allmentorquestions) > 0;
$haslearningnuggetcomments = count($learningnuggetcomments) > 0;

echo $OUTPUT->render_from_template('local_learningcompanions/mentor/mentor_index', array(
    'hasaskedquestions' => $hasaskedquestions,
    'hasmentorquestions' => $hasmentorquestions,
    'hasallmentorquestions' => $hasallmentorquestions,
    'askedquestions' => array_values($askedquestions),
    'mymentorquestions' => array_values($mymentorquestions),
    'allmentorquestions' => array_values($allmentorquestions),
    'ismentor' => \local_learningcompanions\mentors::is_mentor(),
    'istutor' => \local_learningcompanions\mentors::is_tutor(),
    'cfg' => $CFG
));

echo $OUTPUT->footer();
