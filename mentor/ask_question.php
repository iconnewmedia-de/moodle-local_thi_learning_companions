<?php

require_once '../../../config.php';
require_once '../lib.php';

require_login();
$context = context_system::instance();
//require_capability('local/learningcompanions:mentor_view', $context);

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot . '/local/learningcompanions/mentor/ask_question.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_learningcompanions/mentor', 'init');
$PAGE->requires->css('/local/learningcompanions/js_lib/DataTables/datatables.min.css');
$PAGE->requires->css('/local/learningcompanions/js_lib/balloon.css');
$PAGE->navbar->add(get_string('navbar_mentors', 'local_learningcompanions'));
$PAGE->navbar->add(get_string('navbar_mentorquestions', 'local_learningcompanions'), new moodle_url('/local/learningcompanions/mentor/index.php'));

$mentor = optional_param('mentor', 0, PARAM_INT);
require_once __DIR__ . '/mentor_question_form.php';
$custumdata = array('mentor' => $mentor);
$question_form = new \local_learningcompanions\mentor\mentor_question_form(null, $custumdata);
if ($data = $question_form->get_data()) {
    try {
        \local_learningcompanions\mentors::add_mentor_question($USER->id, $data->mentor, $data->questiontopic, $data->subject, $data->question['text']);
        redirect('/local/learningcompanions/mentor/my_questions_to_mentors.php', get_string('mentor_question_added', 'local_learningcompanions'), null,\core\output\notification::NOTIFY_SUCCESS);
//        \core\notification::success(get_string('mentor_question_added', 'local_learningcompanions'));
    } catch(\Exception $e) {
        \core\notification::error(get_string('mentorship_error_unknown', 'local_learningcompanions', $e->getMessage()));
    }
}
echo $OUTPUT->header();
$form = $question_form->render();
echo $OUTPUT->render_from_template('local_learningcompanions/mentor/mentor_ask_question',
    array(
        'form' => $form,
        'cfg' => $CFG
    )
);
echo $OUTPUT->footer();
