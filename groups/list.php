<?php

require_once __DIR__ . "/../../../config.php";

require_login();
global $PAGE, $CFG, $OUTPUT, $USER;
$PAGE->set_context(\context_system::instance());
$PAGE->set_url($CFG->wwwroot . '/local/thi_learning_companions/groups/list.php');
$defaultlayout = 'base';
$layout = optional_param('layout', $defaultlayout, PARAM_TEXT);
$layoutwhitelist = ['base', 'standard', 'course', 'incourse', 'popup', 'embedded'];
if (!in_array($layout, $layoutwhitelist)) {
    $layout = $defaultlayout;
}
$PAGE->set_pagelayout($layout);
$PAGE->set_title(get_string('listgroups', 'local_thi_learning_companions'));

$cmid = optional_param('cmid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);

$groups = local_thi_learning_companions\groups::get_groups_of_user($USER->id);
$groups = array_values($groups);
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_thi_learning_companions/group_list', array(
    'groups' => $groups,
    'cfg' => $CFG
));
echo $OUTPUT->footer();