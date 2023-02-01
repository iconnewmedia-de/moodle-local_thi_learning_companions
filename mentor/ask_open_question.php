<?php

require_once dirname(__DIR__, 3) . '/config.php';

global $PAGE, $CFG, $OUTPUT;

require_login();

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot . '/local/learningcompanions/mentor/ask_open_question.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('ask_open_question', 'local_learningcompanions'));
$PAGE->set_heading(get_string('ask_open_question', 'local_learningcompanions'));

$form = new \local_learningcompanions\forms\ask_open_question();

if ($data = $form->get_data()) {
    ['question' => $questionArr, 'subject' => $subject, 'topic' => $topic] = (array)$data;
    $question = $questionArr['text'];

    \local_learningcompanions\question::ask_new_open_question($question, $subject, $topic);

    redirect(new moodle_url('/local/learningcompanions/mentor/'));
}

echo $OUTPUT->header();
echo $form->render();
echo $OUTPUT->footer();
