<?php
require_once '../../../config.php';
require_once '../lib.php';

$context = context_system::instance();
require_login();
$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/learningcompanions/mentor/mentor_questions_overview.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_learningcompanions/mentor', 'init');
$PAGE->requires->css('/local/learningcompanions/vendor/DataTables/datatables.min.css');
$PAGE->requires->css('/local/learningcompanions/vendor/balloon.css');
$PAGE->navbar->add(get_string('navbar_mentors', 'local_learningcompanions'));
$PAGE->navbar->add(get_string('navbar_mentorquestions', 'local_learningcompanions'), new moodle_url('/local/learningcompanions/mentor/index.php'));

$questionsToMe = \local_learningcompanions\mentors::get_all_mentor_questions($USER->id,  null, false, true);
$questionsToMe = array_values($questionsToMe);
$myTopics = \local_learningcompanions\mentors::get_mentorship_topics($USER->id);
$questionsToAllMentors = \local_learningcompanions\mentors::get_all_mentor_questions(null, $myTopics, false, true);
$questionsToAllMentors = array_values($questionsToAllMentors);
$hasQuestionsToMe = count($questionsToMe) > 0;
$hasQuestionsToAllMentors = count($questionsToAllMentors) > 0;
$learningNuggetComments = \local_learningcompanions\mentors::get_learning_nugget_comments();
$learningNuggetComments = array_values($learningNuggetComments);
$hasComments = count($learningNuggetComments) > 0;
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_learningcompanions/mentor/mentor_questions_overview', array(
    'questionstome' => $questionsToMe,
    'hasquestionstome' => $hasQuestionsToMe,
    'questionstoallmentors' => $questionsToAllMentors,
    'hasquestionstoallmentors' => $hasQuestionsToAllMentors,
    'latestcomments' => $learningNuggetComments,
    'hascomments' => $hasComments,
    'cfg' => $CFG
));

echo $OUTPUT->footer();

