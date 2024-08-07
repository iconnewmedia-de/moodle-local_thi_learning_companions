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

require_once((dirname(__DIR__, 2)).'/config.php');
require_login();
global $USER, $DB;

$groupid = required_param('groupid', PARAM_INT);

$errorcode = \local_thi_learning_companions\groups::join_group($USER->id, $groupid);

// If the group is closed, a request has been sent. If this is the case, redirect to the group search page.
$group = $DB->get_record('local_thi_learning_companions_groups', ['id' => $groupid]);
if ($group && $group->closedgroup) {
    redirect(
        new moodle_url('/local/thi_learning_companions/group/search.php'),
        get_string('request_sent', 'local_thi_learning_companions')
    );
} else {
    redirect(
        new moodle_url('/local/thi_learning_companions/chat.php',
            ['groupid' => $groupid]
        )
    );
}
