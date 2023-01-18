<?php
require_once '../../../config.php';
require_once '../lib.php';

$context = context_system::instance();
require_capability( 'local/learningcompanions:group_search', $context);

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/learningcompanions/group/search.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_learningcompanions/group', 'init');
$PAGE->requires->js_call_amd('local_learningcompanions/group', 'select2');
$PAGE->requires->css('/local/learningcompanions/vendor/DataTables/datatables.min.css');
$PAGE->requires->css('/local/learningcompanions/vendor/select2.min.css');
$PAGE->requires->css('/local/learningcompanions/vendor/balloon.css');
$PAGE->navbar->add(get_string('navbar_groups', 'local_learningcompanions'), new moodle_url('/local/learningcompanions/group/index.php'));
$PAGE->navbar->add(get_string('navbar_findgroups', 'local_learningcompanions'), new moodle_url('/local/learningcompanions/group/search.php'));

$groups = \local_learningcompanions\groups::get_all_groups(true);
$hasgroups = count($groups) > 0;

echo $OUTPUT->header();
$creategroupurl = $CFG->wwwroot . '/local/learningcompanions/group/create.php';
$courseid = optional_param('courseid', 0, PARAM_INT);
$coursenamefilter = '';
if ($courseid > 0) {
    $creategroupurl .= '?courseid=' . $courseid;
    $coursenamefilter = $DB->get_field('course', 'fullname', array('id' => $courseid));
}
echo $OUTPUT->render_from_template('local_learningcompanions/group/group_search', array(
    'cfg' => $CFG,
    'groups' => array_values($groups),
    'hasgroups' => $hasgroups,
    'keywords' => \local_learningcompanions\groups::get_all_keywords(),
    'creategroupurl' => $creategroupurl,
    'coursenamefilter' => $coursenamefilter
));
echo $OUTPUT->footer();
