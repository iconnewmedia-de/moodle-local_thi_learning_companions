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
require_once('../../../config.php');;
require_once('../lib.php');;

require_login();
$context = context_system::instance();
//require_capability('local/thi_learning_companions:mentor_view', $context);

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot . '/local/thi_learning_companions/mentor/ask_question.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_thi_learning_companions/mentor', 'init');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/DataTables/datatables.min.css');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/balloon.css');
$PAGE->navbar->add(get_string('navbar_mentors', 'local_thi_learning_companions'));
$PAGE->navbar->add(get_string('navbar_mentorquestions', 'local_thi_learning_companions'), new moodle_url('/local/thi_learning_companions/mentor/index.php'));

$mentor = optional_param('mentor', 0, PARAM_INT);
require_once(__DIR__ . '/mentor_question_form.php');;
$custumdata = array('mentor' => $mentor);
$question_form = new \local_thi_learning_companions\mentor\mentor_question_form(null, $custumdata);
if ($data = $question_form->get_data()) {
    try {
        \local_thi_learning_companions\mentors::add_mentor_question($USER->id, $data->mentor, $data->questiontopic, $data->subject, $data->question['text']);
        redirect('/local/thi_learning_companions/mentor/my_questions_to_mentors.php', get_string('mentor_question_added', 'local_thi_learning_companions'), null,\core\output\notification::NOTIFY_SUCCESS);
//        \core\notification::success(get_string('mentor_question_added', 'local_thi_learning_companions'));
    } catch (\Exception $e) {
        \core\notification::error(get_string('mentorship_error_unknown', 'local_thi_learning_companions', $e->getMessage()));
    }
}
echo $OUTPUT->header();
$form = $question_form->render();
echo $OUTPUT->render_from_template('local_thi_learning_companions/mentor/mentor_ask_question',
    array(
        'form' => $form,
        'cfg' => $CFG
    )
);
echo $OUTPUT->footer();
