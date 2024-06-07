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
define('AJAX_SCRIPT', true);

require_once(dirname(__DIR__, 3). '/config.php');
require_login();
require_once(dirname(__DIR__) . "/locallib.php");
global $DB, $PAGE, $CFG;
require_once($CFG->libdir . '/filelib.php');

$context = context_system::instance();
$PAGE->set_context($context);

$customdata = [
  // ICTODO: fill with data if necessary.
];
$form = new local_thi_learning_companions\chat_post_form(null, $customdata);
if ($data = $form->get_data()) {
    // ICTODO: save the form data.
    $status = local_thi_learning_companions\chat_handle_submission($data);
    if ($status["success"]) {
        http_response_code(200);
    } else {
        http_response_code(400);
        echo json_encode($status);
    }
} else {
    http_response_code(400);
}

$status['itemid'] = file_get_unused_draft_itemid();
echo json_encode($status);
die();
