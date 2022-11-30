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

/**
 * @package     local_learningcompanions
 * @copyright   2022 ICON Vernetzte Kommunikation GmbH <info@iconnewmedia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once((dirname(__DIR__, 2)).'/config.php');

global $CFG, $DB, $OUTPUT;

$action = required_param('action', PARAM_TEXT);

switch ($action) {
    case 'deletemyquestion':
        deleteQuetion();
        break;
    case 'getgroupdetails':
        getGroupDetails();
        break;
    case 'leavegroup':
        leaveGroup();
        break;
    case 'requestgroupjoin':
        requestGroupJoin();
        break;
    case 'joingroup':
        joinGroup();
        break;
}

function deleteQuetion() {
    $questionid = required_param('questionid', PARAM_INT);

    if (\local_learningcompanions\mentors::delete_asked_question($questionid)) {
        echo '1';
    } else {
        echo 'fail';
    }
}

function getGroupDetails() {
    global $OUTPUT, $CFG;
    $groupid = required_param('groupid', PARAM_INT);
    $group = \local_learningcompanions\groups::get_group_by_id($groupid);

    echo json_encode($OUTPUT->render_from_template('local_learningcompanions/group/group_modal_groupdetails', [
        'group' => $group,
        'groupadmins' => $group->admins,
        'cfg' => $CFG
    ]), JSON_THROW_ON_ERROR);
}

function leaveGroup() {
    global $USER;
    $groupid = required_param('groupid', PARAM_INT);
    $leaved = \local_learningcompanions\groups::leave_group($USER->id, $groupid);

    if ($leaved) {
        echo '0';
    } else {
        echo '1';
    }
}

function requestGroupJoin() {
    global $USER;

    $groupid = required_param('groupid', PARAM_INT);

    try {
        $requested = \local_learningcompanions\groups::request_group_join($USER->id, $groupid);
    } catch (dml_write_exception $e) {
        echo '1';
        return;
    }

    if ($requested) {
        echo '0';
    } else {
        echo '1';
    }
}

function joinGroup() {
    global $USER;

    $groupid = required_param('groupid', PARAM_INT);

    try {
        $joined = \local_learningcompanions\groups::join_group($USER->id, $groupid);
    } catch (dml_write_exception $e) {
        echo '1';
        return;
    }

    if ($joined) {
        echo '0';
    } else {
        echo '1';
    }
}
