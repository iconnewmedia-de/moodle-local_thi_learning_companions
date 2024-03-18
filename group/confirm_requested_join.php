<?php
require_once dirname(__DIR__, 3).'/config.php';
require_once dirname(__DIR__).'/lib.php';

global $PAGE, $CFG, $OUTPUT;

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/thi_learning_companions/group/confirm_requested_join.php');
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('navbar_groups', 'local_thi_learning_companions'), new moodle_url('/local/thi_learning_companions/group/index.php'));
$PAGE->navbar->add(get_string('navbar_confirm_join', 'local_thi_learning_companions'), new moodle_url('/local/thi_learning_companions/group/confirm_requested_join.php'));

$requestForm = new \local_thi_learning_companions\forms\proccess_open_join_requests_form();

if ($data = $requestForm->get_data()) {
    $data = $requestForm->get_data();
    $openRequests = \local_thi_learning_companions\groups::get_group_join_requests();
    foreach ($openRequests as $request) {
        if (isset($data->{'request_' . $request->id . '_action'})) {
            if ($data->{'request_' . $request->id . '_action'} === 'accept') {
                \local_thi_learning_companions\groups::accept_group_join_request($request->id);
            } else {
                \local_thi_learning_companions\groups::deny_group_join_request($request->id);
            }
        }
    }
}

$requestForm = new \local_thi_learning_companions\forms\proccess_open_join_requests_form();

echo $OUTPUT->header();
$templateContext = array('form' => $requestForm->render());
echo $OUTPUT->render_from_template('local_thi_learning_companions/group/group_confirm_requested_join', $templateContext);
echo $OUTPUT->footer();
