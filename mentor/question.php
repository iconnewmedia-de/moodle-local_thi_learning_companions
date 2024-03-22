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
require_once(dirname(__DIR__, 3) . '/config.php');
require_login();
$questionid = required_param('id', PARAM_INT);

global $PAGE, $CFG, $OUTPUT, $USER;
$PAGE->set_context(\context_system::instance());
$PAGE->set_url($CFG->wwwroot . '/local/thi_learning_companions/mentor/question.php');
$PAGE->set_pagelayout('base');
$PAGE->set_title(get_string('learninggroups', 'local_thi_learning_companions'));
$groupid = optional_param('groupid', null, PARAM_INT);
$PAGE->requires->js_call_amd('local_thi_learning_companions/thi_learning_companions_chat', 'init');

$PAGE->requires->js(new moodle_url('https://unpkg.com/react@18/umd/react.development.js'), true);
$PAGE->requires->js(new moodle_url('https://unpkg.com/react-dom@18/umd/react-dom.development.js'), true);
$PAGE->requires->js(new moodle_url('/local/thi_learning_companions/js/react/build/thi_learning_companions-chat.min.js'));

$chat = \local_thi_learning_companions\chat::create_question_chat($questionid);

echo $OUTPUT->header();
echo $chat->get_question_chat_module();
echo $OUTPUT->footer();
