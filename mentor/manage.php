<?php
// ICTODO: create a page for managing a user's mentorships:
// Which courses have I qualified for?
// Which courses have I agreed to become a mentor for?

require_once dirname(__DIR__, 3).'/config.php';
require_once dirname(__DIR__).'/lib.php';

$context = context_system::instance();
require_login();

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot . '/local/learningcompanions/mentor/manage.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('manage_mentorships', 'local_learningcompanions'));
$PAGE->requires->js_call_amd('local_learningcompanions/mentor', 'init');
$PAGE->requires->css('/local/learningcompanions/js_lib/DataTables/datatables.min.css');
$PAGE->requires->css('/local/learningcompanions/js_lib/balloon.css');
$PAGE->navbar->add(get_string('navbar_mentors', 'local_learningcompanions'));
$PAGE->navbar->add(get_string('navbar_mentorquestions', 'local_learningcompanions'), new moodle_url('/local/learningcompanions/mentor/index.php'));

$action = optional_param('action', '', PARAM_TEXT);
if ($action === 'acceptmentorship') {
    $topic = required_param('topic', PARAM_TEXT);
    \local_learningcompanions\mentors::assign_mentorship($USER->id, $topic);
}

$qualifications = \local_learningcompanions\mentors::get_new_mentorship_qualifications();
$mentorships = \local_learningcompanions\mentors::get_mentorship_topics();

array_walk($qualifications, function(&$obj) {
   $obj = ["name" => $obj, "name_urlencoded" => urlencode($obj)];
});
array_walk($mentorships, function(&$obj) {
    $obj = ["name" => $obj];
});
$hasQualifications = count($qualifications) > 0;
$hasMentorships = count($mentorships) > 0;

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_learningcompanions/mentor/mentor_manage', [
    'qualifications' => $qualifications,
    'mentorships' => $mentorships,
    'hasqualifications' => $hasQualifications,
    'hasmentorships' => $hasMentorships,
    'cfg' => $CFG
]);

echo $OUTPUT->footer();
