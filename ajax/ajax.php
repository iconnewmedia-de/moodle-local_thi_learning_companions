<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

define('AJAX_SCRIPT', true);

/**
 * Das Projekt THISuccessAI (FBM202-EA-1690-07540) wird im Rahmen der Förderlinie „Hochschulen durch Digitalisierung stärken“
 * durch die Stiftung Innovation in der Hochschulehre gefördert.
 *
 * @package     local_thi_learning_companions
 * @copyright   2022 ICON Vernetzte Kommunikation GmbH <info@iconnewmedia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__DIR__, 3).'/config.php');
require_once(dirname(__DIR__, 1).'/classes/ajaxactions.php');
require_login();

global $CFG, $DB, $OUTPUT, $PAGE;

$PAGE->set_context(context_system::instance());

$action = required_param('action', PARAM_TEXT);
require_sesskey();
switch ($action) {
    case 'deletemyquestion':
        local_thi_learning_companions\AjaxActions::delete_question();
        break;
    case 'getgroupdetails':
        local_thi_learning_companions\AjaxActions::get_group_details();
        break;
    case 'leavegroup':
        local_thi_learning_companions\AjaxActions::leave_group();
        break;
    case 'requestgroupjoin':
        local_thi_learning_companions\AjaxActions::request_group_join();
        break;
    case 'joingroup':
        local_thi_learning_companions\AjaxActions::join_group();
        break;
    case 'getpossiblenewadmins':
        local_thi_learning_companions\AjaxActions::get_possible_new_admins();
        break;
    case 'getinvitableusers':
        local_thi_learning_companions\AjaxActions::get_invitable_users();
        break;
}
