<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
require_once(dirname(__DIR__, 3).'/config.php');;
require_once(dirname(__DIR__).'/lib.php');;

$context = context_system::instance();
require_login();
$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/thi_learning_companions/mentor/mentor_questions_overview.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_thi_learning_companions/mentor', 'init');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/DataTables/datatables.min.css');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/balloon.css');
$PAGE->navbar->add(get_string('navbar_mentors', 'local_thi_learning_companions'));
$PAGE->navbar->add(get_string('navbar_mentorquestions', 'local_thi_learning_companions'), new moodle_url('/local/thi_learning_companions/mentor/index.php'));

$questionsToMe = \local_thi_learning_companions\mentors::get_mentor_questions_by_user_id($USER->id);
$questionsToMe = array_values($questionsToMe);
$myTopics = \local_thi_learning_companions\mentors::get_mentorship_topics($USER->id);
$questionsToAllMentors = \local_thi_learning_companions\mentors::get_mentor_questions_by_topics($myTopics);
$questionsToAllMentors = array_values($questionsToAllMentors);
$hasQuestionsToMe = count($questionsToMe) > 0;
$hasQuestionsToAllMentors = count($questionsToAllMentors) > 0;
$learningNuggetComments = \local_thi_learning_companions\mentors::get_learning_nugget_comments();
$learningNuggetComments = array_values($learningNuggetComments);
$hasComments = count($learningNuggetComments) > 0;

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_thi_learning_companions/mentor/mentor_questions_overview', array(
    'questionstome' => $questionsToMe,
    'hasquestionstome' => $hasQuestionsToMe,
    'questionstoallmentors' => $questionsToAllMentors,
    'hasquestionstoallmentors' => $hasQuestionsToAllMentors,
    'latestcomments' => $learningNuggetComments,
    'hascomments' => $hasComments,
    'cfg' => $CFG
));
echo $OUTPUT->footer();

