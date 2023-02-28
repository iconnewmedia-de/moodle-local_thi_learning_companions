<?php
require_once '../../../config.php';
require_once '../lib.php';

$context = context_system::instance();
//require_capability( 'local/learningcompanions:mentor_search', $context);
require_login();
$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/learningcompanions/mentor/search.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_learningcompanions/mentor', 'init');
$PAGE->requires->js_call_amd('local_learningcompanions/mentor', 'select2');
$PAGE->requires->css('/local/learningcompanions/js_lib/DataTables/datatables.min.css');
$PAGE->requires->css('/local/learningcompanions/js_lib/select2.min.css');
$PAGE->requires->css('/local/learningcompanions/js_lib/balloon.css');
$PAGE->navbar->add(get_string('navbar_mentors', 'local_learningcompanions'), new moodle_url('/local/learningcompanions/mentor/index.php'));
$PAGE->navbar->add(get_string('navbar_findmentors', 'local_learningcompanions'), new moodle_url('/local/learningcompanions/mentor/search.php'));

$mentors = \local_learningcompanions\mentors::get_mentors(null, false, true);
$hasmentors = count($mentors) > 0;
$availableBadges = \local_learningcompanions\mentors::get_selectable_badgetypes($mentors);
$topics = \local_learningcompanions\mentors::get_mentorship_topics_of_mentors($mentors);
$topics = array_values($topics);
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_learningcompanions/mentor/mentor_search', array(
    'cfg' => $CFG,
    'mentors' => array_values($mentors),
    'hasmentors' => $hasmentors,
    'badges' => $availableBadges,
    'topics' => $topics,
));
echo $OUTPUT->footer();
