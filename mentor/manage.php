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
// ICTODO: create a page for managing a user's mentorships:
// Which courses have I qualified for?
// Which courses have I agreed to become a mentor for?

require_once(dirname(__DIR__, 3).'/config.php');
require_once(dirname(__DIR__).'/lib.php');

$context = context_system::instance();
require_login();

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot . '/local/thi_learning_companions/mentor/manage.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('manage_mentorships', 'local_thi_learning_companions'));
$PAGE->requires->js_call_amd('local_thi_learning_companions/mentor', 'init');
$PAGE->requires->css('/local/thi_learning_companions/js/DataTables/datatables.min.css');
$PAGE->requires->css('/local/thi_learning_companions/css/balloon.css');
$PAGE->navbar->add(
    get_string('navbar_mentors', 'local_thi_learning_companions')
);
$PAGE->navbar->add(
    get_string('navbar_mentorquestions', 'local_thi_learning_companions'),
    new moodle_url('/local/thi_learning_companions/mentor/index.php')
);

$action = optional_param('action', '', PARAM_TEXT);
if ($action === 'acceptmentorship') {
    $topic = required_param('topic', PARAM_TEXT);
    \local_thi_learning_companions\mentors::assign_mentorship($USER->id, $topic);
}

$qualifications = \local_thi_learning_companions\mentors::get_new_mentorship_qualifications();
$mentorships = \local_thi_learning_companions\mentors::get_mentorship_topics();

array_walk($qualifications, function(&$obj) {
    $obj = ["name" => $obj, "name_urlencoded" => urlencode($obj)];
});
array_walk($mentorships, function(&$obj) {
    $obj = ["name" => $obj];
});
$hasqualifications = count($qualifications) > 0;
$hasmentorships = count($mentorships) > 0;

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_thi_learning_companions/mentor/mentor_manage', [
    'qualifications' => $qualifications,
    'mentorships' => $mentorships,
    'hasqualifications' => $hasqualifications,
    'hasmentorships' => $hasmentorships,
    'cfg' => $CFG,
]);

echo $OUTPUT->footer();
