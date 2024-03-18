<?php
require_once dirname(__DIR__, 3).'/config.php';

require_login();
global $PAGE, $CFG, $OUTPUT;
$PAGE->set_context(\context_system::instance());
$PAGE->set_url($CFG->wwwroot . '/local/thi_learning_companions/creategroup.php');
$defaultlayout = 'base';
$layout = optional_param('layout', $defaultlayout, PARAM_TEXT);
$layoutwhitelist = ['base', 'standard', 'course', 'incourse', 'popup', 'embedded'];
if (!in_array($layout, $layoutwhitelist)) {
    $layout = $defaultlayout;
}
$PAGE->set_pagelayout($layout);
$PAGE->set_title(get_string('edit_group', 'local_thi_learning_companions'));
$PAGE->navbar->add(get_string('navbar_groups', 'local_thi_learning_companions'), new moodle_url('/local/thi_learning_companions/group/index.php'));
$PAGE->navbar->add(get_string('navbar_edit_group', 'local_thi_learning_companions'), new moodle_url('/local/thi_learning_companions/group/search.php'));

$groupid = required_param('groupid', PARAM_INT);
$group = new \local_thi_learning_companions\group($groupid);

if (!$group->may_edit) {
    print_error(get_string('group_edit_not_allowed', 'local_thi_learning_companions'));
}
// ICTODO: check that the user has the permission to edit this group

$form = new \local_thi_learning_companions\forms\create_edit_group_form(
    null,
    [
        'groupid' => $groupid,
        'cmid' => $group->cmid,
        'courseid' => $group->courseid
    ]
);

$form->setGroupData($group);
$referrer = optional_param('referrer', 'groupsearch', PARAM_TEXT);
$groupid = required_param('groupid', PARAM_INT);
$redirect = false;
$redirectMessage = '';
$redirectMessageType = \core\output\notification::NOTIFY_SUCCESS;
if ($form->is_cancelled()) {
    $redirect = true;
} elseif ($data = $form->get_data()) {
    try {
        \local_thi_learning_companions\groups::group_update(
            $data->groupid,
            $data->name,
            $data->description_editor['text'],
            $data->closedgroup,
            $data->keywords,
            $data->courseid,
            $data->cmid,
            $data->groupimage
        );
        if ($layout === 'popup' || $layout === 'embedded') {
            echo "<script>document.querySelector('.modal').dispatchEvent((new Event('modal:hidden')))</script>";
        } else {
            $redirect = true;
            $redirectMessage = get_string('group_edited', 'local_thi_learning_companions');
        }
    } catch(Exception $e) {
        $warning = new \core\output\notification(
            get_string('error_group_edit_failed', 'local_thi_learning_companions', $e->getMessage()),
            \core\output\notification::NOTIFY_ERROR
        );
    }
    // ICTODO: handle exceptions and output a warning if exception thrown or groupid === false
    // ICTODO: redirect user if everything went well and output a success message
}
if ($redirect) {
    switch($referrer) {
        case 'chat':
            redirect(new moodle_url('/local/thi_learning_companions/chat.php?groupid=' . $groupid), $redirectMessage, null, $redirectMessageType);
            break;
        case 'groupsearch':
        default:
            redirect(new moodle_url('/local/thi_learning_companions/group/search.php'), $redirectMessage, null, $redirectMessageType);
            break;
    }
}

echo $OUTPUT->header();
if (isset($warning)) {
    echo $OUTPUT->render($warning);
}
echo $form->render();
echo $OUTPUT->footer();
