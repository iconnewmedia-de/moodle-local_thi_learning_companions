<?php
require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/classes/comment.php");
require_login();
global $PAGE, $CFG, $OUTPUT, $USER;
$PAGE->set_context(\context_system::instance());
$PAGE->set_url($CFG->wwwroot . '/local/learningcompanions/chat.php');
$defaultlayout = 'base';
$layout = optional_param('layout', $defaultlayout, PARAM_TEXT);
$layoutwhitelist = ['base', 'standard', 'course', 'incourse', 'popup', 'embedded'];
if (!in_array($layout, $layoutwhitelist)) {
    $layout = $defaultlayout;
}
$PAGE->set_pagelayout($layout);
$PAGE->set_title(get_string('learninggroups', 'local_learningcompanions'));
ob_start();
echo $OUTPUT->header();
$commentid = required_param('commentid', PARAM_INT);
// make sure that this comment actually belongs to the user
$comment = new local_learningcompanions\comment($commentid);
global $USER;
if ($comment->userid !== $USER->id) {
    throw new \moodle_exception('error_edit_other_users_comment', 'local_learningcompanions');
    die();
}
$form = new local_learningcompanions\chat_post_form();
$form->set_data($comment);
if ($data = $form->get_data()){
    // handle form submission
} else {
    // might not need this else section
}
$form->display();
echo $OUTPUT->footer();
$output = ob_get_clean();
echo json_encode(['data' => $output]);