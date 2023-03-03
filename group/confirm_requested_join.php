<?php
require_once dirname(__DIR__, 3).'/config.php';
require_once dirname(__DIR__).'/lib.php';

global $PAGE, $CFG, $OUTPUT;

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/learningcompanions/group/confirm_requested_join.php');
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('navbar_groups', 'local_learningcompanions'), new moodle_url('/local/learningcompanions/group/index.php'));
$PAGE->navbar->add(get_string('navbar_confirm_join', 'local_learningcompanions'), new moodle_url('/local/learningcompanions/group/confirm_requested_join.php'));

$requestForm = new \local_learningcompanions\forms\proccess_open_join_requests_form();

if ($data = $requestForm->get_data()) {
    $data = $requestForm->get_data();
    $openRequests = \local_learningcompanions\groups::get_group_join_requests();
    foreach ($openRequests as $request) {
        if (isset($data->{'request_' . $request->id . '_action'})) {
            if ($data->{'request_' . $request->id . '_action'} === 'accept') {
                \local_learningcompanions\groups::accept_group_join_request($request->id);
            } else {
                \local_learningcompanions\groups::deny_group_join_request($request->id);
            }
        }
    }
}

$requestForm = new \local_learningcompanions\forms\proccess_open_join_requests_form();

echo $OUTPUT->header();
$templateContext = array('form' => $requestForm->render());
echo $OUTPUT->render_from_template('local_learningcompanions/group/group_confirm_requested_join', $templateContext);
echo $OUTPUT->footer();
