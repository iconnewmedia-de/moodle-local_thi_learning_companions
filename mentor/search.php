<?php

require_once dirname(__DIR__,3).'/config.php';
require_once dirname(__DIR__).'/lib.php';

$context = context_system::instance();
//require_capability( 'local/thi_learning_companions:mentor_search', $context);
require_login();
$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/thi_learning_companions/mentor/search.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_thi_learning_companions/mentor', 'init');
$PAGE->requires->js_call_amd('local_thi_learning_companions/mentor', 'select2');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/DataTables/datatables.min.css');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/select2.min.css');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/balloon.css');
$PAGE->navbar->add(get_string('navbar_mentors', 'local_thi_learning_companions'), new moodle_url('/local/thi_learning_companions/mentor/index.php'));
$PAGE->navbar->add(get_string('navbar_findmentors', 'local_thi_learning_companions'), new moodle_url('/local/thi_learning_companions/mentor/search.php'));

$mentors = \local_thi_learning_companions\mentors::get_mentors(null, false, true);
$hasmentors = count($mentors) > 0;
$availableBadges = \local_thi_learning_companions\mentors::get_selectable_badgetypes($mentors);
$topics = \local_thi_learning_companions\mentors::get_mentorship_topics_of_mentors($mentors);
$topics = array_values($topics);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_thi_learning_companions/mentor/mentor_search', array(
    'cfg' => $CFG,
    'mentors' => array_values($mentors),
    'hasmentors' => $hasmentors,
    'badges' => $availableBadges,
    'topics' => $topics,
));
echo $OUTPUT->footer();
