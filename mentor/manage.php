<?php
// ICTODO: create a page for managing a user's mentorships:
// Which courses have I qualified for?
// Which courses have I agreed to become a mentor for?

require_once dirname(__DIR__, 3).'/config.php';
require_once dirname(__DIR__).'/lib.php';

$context = context_system::instance();
require_login();

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot . '/local/thi_learning_companions/mentor/manage.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('manage_mentorships', 'local_thi_learning_companions'));
$PAGE->requires->js_call_amd('local_thi_learning_companions/mentor', 'init');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/DataTables/datatables.min.css');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/balloon.css');
$PAGE->navbar->add(get_string('navbar_mentors', 'local_thi_learning_companions'));
$PAGE->navbar->add(get_string('navbar_mentorquestions', 'local_thi_learning_companions'), new moodle_url('/local/thi_learning_companions/mentor/index.php'));

$action = optional_param('action', '', PARAM_TEXT);
if ($action === 'acceptmentorship') {
    $topic = required_param('topic', PARAM_TEXT);
    \local_thi_learning_companions\mentors::assign_mentorship($USER->id, $topic);
}

$qualifications = \local_thi_learning_companions\mentors::get_new_mentorship_qualifications();
$mentorships = \local_thi_learning_companions\mentors::get_mentorship_topics();

array_walk($qualifications, function(&$obj) {
   $obj = ["name" => $obj, "name_urlencoded" => urlencode($obj)];
});
array_walk($mentorships, function(&$obj) {
    $obj = ["name" => $obj];
});
$hasQualifications = count($qualifications) > 0;
$hasMentorships = count($mentorships) > 0;

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_thi_learning_companions/mentor/mentor_manage', [
    'qualifications' => $qualifications,
    'mentorships' => $mentorships,
    'hasqualifications' => $hasQualifications,
    'hasmentorships' => $hasMentorships,
    'cfg' => $CFG
]);

echo $OUTPUT->footer();
