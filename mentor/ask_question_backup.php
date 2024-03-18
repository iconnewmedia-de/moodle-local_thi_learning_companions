<?php

require_once dirname(__DIR__, 3) . '/config.php';

global $PAGE, $CFG, $OUTPUT;

require_login();

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot . '/local/thi_learning_companions/mentor/ask_open_question.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('ask_open_question', 'local_thi_learning_companions'));
$PAGE->set_heading(get_string('ask_open_question', 'local_thi_learning_companions'));

$form = new \local_thi_learning_companions\forms\ask_open_question();

if ($data = $form->get_data()) {
    ['question' => $questionArr, 'subject' => $subject, 'topic' => $topic] = (array)$data;
    $question = $questionArr['text'];

    \local_thi_learning_companions\question::ask_new_open_question($question, $subject, $topic);

    redirect(new moodle_url('/local/thi_learning_companions/mentor/'),
        get_string('question_asked', 'local_thi_learning_companions'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
    die();
}

echo $OUTPUT->header();
echo $form->render();
echo $OUTPUT->footer();
