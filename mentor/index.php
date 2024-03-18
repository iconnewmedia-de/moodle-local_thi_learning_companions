<?php

use local_thi_learning_companions\mentors;

require_once dirname(__DIR__, 3).'/config.php';
require_once dirname(__DIR__).'/lib.php';

require_login();

$context = context_system::instance();
//require_capability( 'local/thi_learning_companions:mentor_view', $context);

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/thi_learning_companions/mentor/index.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_thi_learning_companions/mentor', 'init');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/DataTables/datatables.min.css');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/balloon.css');
$PAGE->navbar->add(get_string('navbar_mentors', 'local_thi_learning_companions'));
$PAGE->navbar->add(get_string('navbar_mentorquestions', 'local_thi_learning_companions'), new moodle_url('/local/thi_learning_companions/mentor/index.php'));

$mentortopics = mentors::get_all_mentor_keywords($USER->id, true);
$askedquestions = mentors::get_my_asked_questions($USER->id);
$mymentorquestions = mentors::get_mentor_questions_by_user_id($USER->id);
$allmentorquestions = mentors::get_mentor_questions_by_topics($mentortopics);
$learningNuggetComments = mentors::get_learning_nugget_comments();

echo $OUTPUT->header();

$notification = optional_param('n', null, PARAM_TEXT);
if (!is_null($notification)) {
    $notificationtype = substr($notification, 0, 2) === 'n_' ? 'error' : 'success';
    echo $OUTPUT->notification(get_string('notification_'.$notification, 'local_thi_learning_companions'), $notificationtype);
}

$hasaskedquestions = count($askedquestions) > 0;
$hasmentorquestions = count($mymentorquestions) > 0;
$hasallmentorquestions = count($allmentorquestions) > 0;
$haslearningnuggetcomments = count($learningNuggetComments) > 0;

echo $OUTPUT->render_from_template('local_thi_learning_companions/mentor/mentor_index', [
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
