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

$context = context_system::instance();
require_login();
$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/thi_learning_companions/mentor/my_questions_to_mentors.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_thi_learning_companions/mentor', 'init');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/DataTables/datatables.min.css');
$PAGE->requires->css('/local/thi_learning_companions/js_lib/balloon.css');
$PAGE->navbar->add(get_string('navbar_mentors', 'local_thi_learning_companions'));
$PAGE->navbar->add(
    get_string('navbar_mentorquestions', 'local_thi_learning_companions'),
    new moodle_url('/local/thi_learning_companions/mentor/index.php')
);

$askedquestions = \local_thi_learning_companions\mentors::get_my_asked_questions($USER->id, true);
$hasaskedquestions = count($askedquestions) > 0;
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_thi_learning_companions/mentor/mentor_myquestions_to_mentors', [
    'hasaskedquestions' => $hasaskedquestions,
    'askedquestions' => array_values($askedquestions),
    'cfg' => $CFG,
]);

echo $OUTPUT->footer();

