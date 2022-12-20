<?php
define('AJAX_SCRIPT', true);
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/classes/chat_post_form.php";
require_once __DIR__ . "/locallib.php";
global $DB, $PAGE;

$context = context_system::instance();
$PAGE->set_context($context);

$customdata = [
  // ICTODO: fill with data if necessary
];
$form = new local_learningcompanions\chat_post_form(null, $customdata);
if ($data = $form->get_data()) {
    // ICTODO: save the form data
    $status = local_learningcompanions\chat_handle_submission($data, $form);
    if ($status["success"]) {
        http_response_code(200);
    } else {
        http_response_code(400);
        echo json_encode($status);
    }
} else {
    http_response_code(400);
}
echo json_encode($status);
die();
