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
global $PAGE, $CFG, $OUTPUT;

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/local/thi_learning_companions/group/confirm_requested_join.php');
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(
    get_string('navbar_groups', 'local_thi_learning_companions'),
    new moodle_url('/local/thi_learning_companions/group/index.php')
);
$PAGE->navbar->add(
    get_string('navbar_confirm_join', 'local_thi_learning_companions'),
    new moodle_url('/local/thi_learning_companions/group/confirm_requested_join.php')
);

$requestform = new \local_thi_learning_companions\forms\proccess_open_join_requests_form();

if ($data = $requestform->get_data()) {
    $data = $requestform->get_data();
    $openrequests = \local_thi_learning_companions\groups::get_group_join_requests();
    foreach ($openrequests as $request) {
        if (isset($data->{'request_' . $request->id . '_action'})) {
            if ($data->{'request_' . $request->id . '_action'} === 'accept') {
                \local_thi_learning_companions\groups::accept_group_join_request($request->id);
            } else {
                \local_thi_learning_companions\groups::deny_group_join_request($request->id);
            }
        }
    }
}

$requestform = new \local_thi_learning_companions\forms\proccess_open_join_requests_form();

echo $OUTPUT->header();
$templatecontext = ['form' => $requestform->render()];
echo $OUTPUT->render_from_template('local_thi_learning_companions/group/group_confirm_requested_join', $templatecontext);
echo $OUTPUT->footer();
