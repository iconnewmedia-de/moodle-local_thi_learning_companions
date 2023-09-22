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
 * Das Projekt THISuccessAI (FBM202-EA-1690-07540) wird im Rahmen der Förderlinie „Hochschulen durch Digitalisierung stärken“ durch die Stiftung Innovation in der Hochschulehre gefördert.
 *
 * @package     local_learningcompanions
 * @copyright   2022 ICON Vernetzte Kommunikation GmbH <info@iconnewmedia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once dirname(__DIR__, 3).'/config.php';

require_login();

global $CFG, $DB, $OUTPUT, $PAGE;

$PAGE->set_context(context_system::instance());

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
    case 'getpossiblenewadmins':
        getPossibleNewAdmins();
        break;
    case 'getinvitableusers':
        getInvitableUsers();
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
    $referrer = optional_param('referrer', 'groupsearch', PARAM_TEXT);
    $group = \local_learningcompanions\groups::get_group_by_id($groupid);
    $mayViewGroupmembers = $group->closedgroup == 0 || \local_learningcompanions\groups::may_view_group($groupid);
    if ($mayViewGroupmembers) {
        $group->groupmembers = array_values($group->groupmembers);
        foreach ($group->groupmembers as $groupmember) {
            list($groupmember->status, $groupmember->statustext) = \local_learningcompanions_get_user_status($groupmember->id);
            $groupmember->userpic = $OUTPUT->user_picture($groupmember, [
                'link' => false, 'visibletoscreenreaders' => false,
                'class' => 'userpicture'
            ]);
        }
    } else {
        $group->groupmembers = [];
    }

    $cm = $group->cm;
    echo json_encode(['html' => $OUTPUT->render_from_template('local_learningcompanions/group/group_modal_groupdetails', [
        'group' => $group,
        'referrer' => $referrer,
        'groupadmins' => $group->admins,
        'cfg' => $CFG,
        'cm' => $cm,
        'groupmembers' => $group->groupmembers,
        'mayviewmembers' => $mayViewGroupmembers
    ])], JSON_THROW_ON_ERROR);
}

function getPossibleNewAdmins() {
    global $USER, $OUTPUT;

    $groupid = required_param('groupid', PARAM_INT);
    $group = \local_learningcompanions\groups::get_group_by_id($groupid);
    $groupmembers = $group->groupmembers;
    $groupmembers = array_filter($groupmembers, function($member) use ($USER) {
        return $member->id !== $USER->id;
    });

    //ICTODO: the first user should be the last active user, because this user is the one, that gets shown the first
    $possibleAdmins = array_map(function($member) {
        return (object)['userid' => $member->id, 'name' => fullname($member)];
    }, $groupmembers);

    echo json_encode($OUTPUT->render_from_template('local_learningcompanions/group/group_modal_select_new_admin', []), JSON_THROW_ON_ERROR);
}

function leaveGroup() {
    global $USER;
    $groupid = required_param('groupid', PARAM_INT);

    $group = new \local_learningcompanions\group($groupid);
    $isAdmin = $group->is_user_admin($USER->id);

    //If
    // - The user is an admin
    // - The user is the only admin
    // - The group has more than one member
    // The user can´t leave the group and has to select a new admin first
    if ($isAdmin && count($group->admins) === 1 && count($group->groupmembers) > 1) {
        $response = ['leaved' => false, 'needsNewAdmin' => true];
    } else if ($group->closedgroup && count($group->groupmembers) === 1) {
        $response = ['leaved' => false, 'isLastMember' => true];
    } else {
        $leaved = \local_learningcompanions\groups::leave_group($USER->id, $groupid);
        $response = ['leaved' => $leaved];
    }

    echo json_encode($response, JSON_THROW_ON_ERROR);
}

function requestGroupJoin() {
    global $USER;

    $groupid = required_param('groupid', PARAM_INT);

    try {
        $errorCode = \local_learningcompanions\groups::request_group_join($USER->id, $groupid);
    } catch (dml_write_exception $e) {
        echo \local_learningcompanions\groups::JOIN_REQUEST_OTHER_ERROR;
        return;
    }

    echo $errorCode;
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

function getInvitableUsers() {
    global $DB, $USER;

    $query = required_param('query', PARAM_TEXT);
    $groupId = required_param('groupid', PARAM_INT);
    $limit = optional_param('limit', 10, PARAM_INT);

    $sl = "SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) as fullname
                FROM {user} u
                LEFT JOIN {groups_members} gm ON gm.userid = u.id
                WHERE u.deleted = 0
                AND u.confirmed = 1
                AND " . $DB->sql_like($DB->sql_fullname(), ':search', false) . "
                AND u.id <> :userid
                AND (gm.groupid <> :groupid OR gm.groupid IS NULL)
              ORDER BY " . $DB->sql_fullname();

    $params = [
        'search' => '%'. $DB->sql_like_escape($query) . '%',
        'userid' => $USER->id,
        'groupid' => $groupId
    ];

    $users = $DB->get_records_sql($sl, $params, 0, $limit);

    echo json_encode(array_values($users), JSON_THROW_ON_ERROR);
}
