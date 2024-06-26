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

/**
 * Das Projekt THISuccessAI (FBM202-EA-1690-07540) wird im Rahmen der Förderlinie „Hochschulen durch Digitalisierung stärken“
 * durch die Stiftung Innovation in der Hochschulehre gefördert.
 *
 * @package     local_thi_learning_companions
 * @copyright   2022 ICON Vernetzte Kommunikation GmbH <info@iconnewmedia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__DIR__, 3).'/config.php');
require_once(dirname(__DIR__).'/lib.php');

$context = context_system::instance();
require_login();
$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/thi_learning_companions/mentor/mentor_questions_overview.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_thi_learning_companions/mentor', 'init');
$PAGE->requires->css('/local/thi_learning_companions/css/DataTables/datatables.min.css');
$PAGE->requires->css('/local/thi_learning_companions/css/balloon.css');
$PAGE->navbar->add(get_string('navbar_mentors', 'local_thi_learning_companions'));
$PAGE->navbar->add(
    get_string('navbar_mentorquestions', 'local_thi_learning_companions'),
    new moodle_url('/local/thi_learning_companions/mentor/index.php')
);

$questionstome = \local_thi_learning_companions\mentors::get_mentor_questions_by_user_id($USER->id);
$questionstome = array_values($questionstome);
$mytopics = \local_thi_learning_companions\mentors::get_mentorship_topics($USER->id);
$questionstoallmentors = \local_thi_learning_companions\mentors::get_open_mentor_questions_by_topics($mytopics);
$questionstoallmentors = array_values($questionstoallmentors);
$hasquestionstome = count($questionstome) > 0;
$hasquestionstoallmentors = count($questionstoallmentors) > 0;
$learningnuggetcomments = \local_thi_learning_companions\mentors::get_learning_nugget_comments();
$learningnuggetcomments = array_values($learningnuggetcomments);
$hascomments = count($learningnuggetcomments) > 0;

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_thi_learning_companions/mentor/mentor_questions_overview', [
    'questionstome' => $questionstome,
    'hasquestionstome' => $hasquestionstome,
    'questionstoallmentors' => $questionstoallmentors,
    'hasquestionstoallmentors' => $hasquestionstoallmentors,
    'latestcomments' => $learningnuggetcomments,
    'hascomments' => $hascomments,
    'cfg' => $CFG,
]);
echo $OUTPUT->footer();

