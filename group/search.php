<?php
require_once '../../../config.php';
require_once '../lib.php';

$context = context_system::instance();
require_capability( 'local/thi_learning_companions:group_search', $context);

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/thi_learning_companions/group/search.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_thi_learning_companions/group', 'init');
$PAGE->requires->js_call_amd('local_thi_learning_companions/group', 'select2');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/DataTables/datatables.min.css');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/select2.min.css');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/balloon.css');
$PAGE->navbar->add(get_string('navbar_groups', 'local_thi_learning_companions'), new moodle_url('/local/thi_learning_companions/group/index.php'));
$PAGE->navbar->add(get_string('navbar_findgroups', 'local_thi_learning_companions'), new moodle_url('/local/thi_learning_companions/group/search.php'));

$groups = \local_thi_learning_companions\groups::get_all_groups();
$hasgroups = count($groups) > 0;

\local_thi_learning_companions\event\group_searched::make($USER->id)->trigger();

echo $OUTPUT->header();
$creategroupurl = $CFG->wwwroot . '/local/thi_learning_companions/group/create.php';
$courseid = optional_param('courseid', 0, PARAM_INT);
$coursenamefilter = '';
if ($courseid > 0) {
    $creategroupurl .= '?courseid=' . $courseid;
    $coursenamefilter = $DB->get_field('course', 'fullname', array('id' => $courseid));
}
$keywords = array_values(\local_thi_learning_companions\groups::get_all_keywords());
echo $OUTPUT->render_from_template('local_thi_learning_companions/group/group_search', array(
    'cfg' => $CFG,
    'groups' => array_values($groups),
    'hasgroups' => $hasgroups,
    'keywords' => $keywords,
    'creategroupurl' => $creategroupurl,
    'coursenamefilter' => $coursenamefilter
));
echo $OUTPUT->footer();
