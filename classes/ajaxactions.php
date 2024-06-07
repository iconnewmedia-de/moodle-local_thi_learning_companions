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

namespace local_thi_learning_companions;

/**
 * Das Projekt THISuccessAI (FBM202-EA-1690-07540) wird im Rahmen der Förderlinie „Hochschulen durch Digitalisierung stärken“
 * durch die Stiftung Innovation in der Hochschulehre gefördert.
 *
 * @package     local_thi_learning_companions
 * @copyright   2022 ICON Vernetzte Kommunikation GmbH <info@iconnewmedia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class AjaxActions {

    /**
     * Deletes a question. Question id must have been passed as required parameter.
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function delete_question() {
        $questionid = required_param('questionid', PARAM_INT);
        require_sesskey();
        if (\local_thi_learning_companions\mentors::delete_asked_question($questionid)) {
            echo '1';
        } else {
            echo 'fail';
        }
    }

    /**
     * Returns all information for a group.
     * Expects groupid as required_param.
     * @return void
     * @throws \JsonException
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_group_details() {
        global $OUTPUT, $CFG;
        $groupid = required_param('groupid', PARAM_INT);
        $referrer = optional_param('referrer', 'groupsearch', PARAM_TEXT);
        $group = \local_thi_learning_companions\groups::get_group_by_id($groupid);
        $mayviewgroupmembers = $group->closedgroup == 0 || \local_thi_learning_companions\groups::may_view_group($groupid);
        if ($mayviewgroupmembers) {
            $group->groupmembers = array_values($group->groupmembers);
            foreach ($group->groupmembers as $groupmember) {
                list($groupmember->status, $groupmember->statustext) =
                    \local_thi_learning_companions_get_user_status($groupmember->id);
                $groupmember->userpic = $OUTPUT->user_picture($groupmember, [
                    'link' => false, 'visibletoscreenreaders' => false,
                    'class' => 'local_thi_learning_companions_usersearch_picture',
                ]);
            }
        } else {
            $group->groupmembers = [];
        }

        $cm = $group->cm;
        echo json_encode(['html' => $OUTPUT->render_from_template('local_thi_learning_companions/group/group_modal_groupdetails', [
            'group' => $group,
            'referrer' => $referrer,
            'groupadmins' => $group->admins,
            'cfg' => $CFG,
            'cm' => $cm,
            'groupmembers' => $group->groupmembers,
            'mayviewmembers' => $mayviewgroupmembers,
        ])], JSON_THROW_ON_ERROR);
    }

    /**
     * Returns a list of potential new administrators for a group.
     * Used for presenting a list of users to assign as admin when the original admin left.
     * @return void
     * @throws \JsonException
     * @throws \coding_exception
     */
    public static function get_possible_new_admins() {
        global $USER, $OUTPUT;

        $groupid = required_param('groupid', PARAM_INT);
        $group = \local_thi_learning_companions\groups::get_group_by_id($groupid);
        $groupmembers = $group->groupmembers;
        $groupmembers = array_filter($groupmembers, function($member) use ($USER) {
            return $member->id !== $USER->id;
        });

        // ICTODO: The first user should be the last active user, because this user is the one, that gets shown the first.
        $possibleadmins = array_map(function($member) {
            return (object)['userid' => $member->id, 'name' => fullname($member)];
        }, $groupmembers);

        echo json_encode(
            $OUTPUT->render_from_template('local_thi_learning_companions/group/group_modal_select_new_admin', []),
            JSON_THROW_ON_ERROR
        );
    }

    /**
     * Handles current user leaving a group.
     * The function expects the required_param groupid.
     * @return void
     * @throws \JsonException
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function leave_group() {
        global $USER;
        $groupid = required_param('groupid', PARAM_INT);

        $group = new \local_thi_learning_companions\group($groupid);
        $isadmin = $group->is_user_admin($USER->id);

        // If
        // - The user is an admin
        // - The user is the only admin
        // - The group has more than one member
        // The user can´t leave the group and has to select a new admin first.
        if ($isadmin && count($group->admins) === 1 && count($group->groupmembers) > 1) {
            $response = ['leaved' => false, 'needsNewAdmin' => true];
        } else if ($group->closedgroup && count($group->groupmembers) === 1) {
            $response = ['leaved' => false, 'isLastMember' => true];
        } else {
            $leaved = \local_thi_learning_companions\groups::leave_group($USER->id, $groupid);
            $response = ['leaved' => $leaved];
        }

        echo json_encode($response, JSON_THROW_ON_ERROR);
    }

    /**
     * Gets called when the current user requests to join a group.
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function request_group_join() {
        global $USER;

        $groupid = required_param('groupid', PARAM_INT);

        try {
            $errorcode = \local_thi_learning_companions\groups::request_group_join($USER->id, $groupid);
        } catch (dml_write_exception $e) {
            echo \local_thi_learning_companions\groups::JOIN_REQUEST_OTHER_ERROR;
            return;
        }

        echo $errorcode;
    }

    /**
     * Gets called when user joins a group.
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function join_group() {
        global $USER;

        $groupid = required_param('groupid', PARAM_INT);

        try {
            $joined = \local_thi_learning_companions\groups::join_group($USER->id, $groupid);
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

    /**
     * Returns a list of users that can be invited.
     * @return void
     * @throws \JsonException
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_invitable_users() {
        global $DB, $USER;

        $query = required_param('query', PARAM_TEXT);
        $groupid = required_param('groupid', PARAM_INT);
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
            'groupid' => $groupid,
        ];

        $users = $DB->get_records_sql($sl, $params, 0, $limit);

        echo json_encode(array_values($users), JSON_THROW_ON_ERROR);
    }

}
