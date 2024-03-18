<?php
require_once '../../../config.php';
require_once '../lib.php';

$context = context_system::instance();
require_login();
$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/thi_learning_companions/mentor/my_questions_to_mentors.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_thi_learning_companions/mentor', 'init');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/DataTables/datatables.min.css');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/balloon.css');
$PAGE->navbar->add(get_string('navbar_mentors', 'local_thi_learning_companions'));
$PAGE->navbar->add(get_string('navbar_mentorquestions', 'local_thi_learning_companions'), new moodle_url('/local/thi_learning_companions/mentor/index.php'));

$askedquestions = \local_thi_learning_companions\mentors::get_my_asked_questions($USER->id, true);
$hasaskedquestions = count($askedquestions) > 0;
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_thi_learning_companions/mentor/mentor_myquestions_to_mentors', array(
    'hasaskedquestions' => $hasaskedquestions,
    'askedquestions' => array_values($askedquestions),
    'cfg' => $CFG
));

echo $OUTPUT->footer();

