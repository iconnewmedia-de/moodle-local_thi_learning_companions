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
require_login();

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/thi_learning_companions/mentor/search.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js_call_amd('local_thi_learning_companions/mentor', 'init');
$PAGE->requires->js_call_amd('local_thi_learning_companions/mentor', 'select2');
$PAGE->requires->css('/local/thi_learning_companions/css/DataTables/datatables.min.css');
$PAGE->requires->css('/local/thi_learning_companions/css/select2.min.css');
$PAGE->requires->css('/local/thi_learning_companions/css/balloon.css');
$PAGE->navbar->add(
    get_string('navbar_mentors', 'local_thi_learning_companions'),
    new moodle_url('/local/thi_learning_companions/mentor/index.php')
);
$PAGE->navbar->add(
    get_string('navbar_findmentors', 'local_thi_learning_companions'),
    new moodle_url('/local/thi_learning_companions/mentor/search.php')
);

$mentors = \local_thi_learning_companions\mentors::get_mentors(null, false, true);
$hasmentors = count($mentors) > 0;
$availablebadges = \local_thi_learning_companions\mentors::get_selectable_badgetypes($mentors);
$topics = \local_thi_learning_companions\mentors::get_mentorship_topics_of_mentors($mentors);
$topics = array_values($topics);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_thi_learning_companions/mentor/mentor_search', [
    'cfg' => $CFG,
    'mentors' => array_values($mentors),
    'hasmentors' => $hasmentors,
    'badges' => $availablebadges,
    'topics' => $topics,
]);
echo $OUTPUT->footer();
