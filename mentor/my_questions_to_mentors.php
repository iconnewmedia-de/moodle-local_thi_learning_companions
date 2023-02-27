<?php
require_once '../../../config.php';
require_once '../lib.php';

$context = context_system::instance();
require_login();
$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/learningcompanions/mentor/my_questions_to_mentors.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_learningcompanions/mentor', 'init');
$PAGE->requires->css('/local/learningcompanions/js_lib/DataTables/datatables.min.css');
$PAGE->requires->css('/local/learningcompanions/js_lib/balloon.css');
$PAGE->navbar->add(get_string('navbar_mentors', 'local_learningcompanions'));
$PAGE->navbar->add(get_string('navbar_mentorquestions', 'local_learningcompanions'), new moodle_url('/local/learningcompanions/mentor/index.php'));

$askedquestions = \local_learningcompanions\mentors::get_my_asked_questions($USER->id, true);
$hasaskedquestions = count($askedquestions) > 0;
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_learningcompanions/mentor/mentor_myquestions_to_mentors', array(
    'hasaskedquestions' => $hasaskedquestions,
    'askedquestions' => array_values($askedquestions),
    'cfg' => $CFG
));

echo $OUTPUT->footer();

