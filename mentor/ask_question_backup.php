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

require_once(dirname(__DIR__, 3) . '/config.php');

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
    ['question' => $questionarr, 'subject' => $subject, 'topic' => $topic] = (array)$data;
    $question = $questionarr['text'];

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
