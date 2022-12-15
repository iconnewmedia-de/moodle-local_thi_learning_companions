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
namespace local_learningcompanions;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
class external extends \external_api {
    public static function list_nuggets($courseid, $query) {
        global $DB;
        $params = self::validate_parameters(self::list_nuggets_parameters(),
            array(
                'courseid' => $courseid,
                'query' => $query,
            ));
        $courseid = $params['courseid'];
        $query = $params['query'];

        file_put_contents(__DIR__ . "/logexternalapi.txt", "called at " . date('d.m.Y H:i:s') . "\r\n", FILE_APPEND);
        if (is_null($courseid)) {
            return [];
        }
        $cminfo = \course_modinfo::instance($courseid);
        $return = [];
        $cms = $cminfo->get_cms();
        foreach($cms as $cm) {
            if (empty($query) || stripos($cm->name, $query) > -1) {
                $return[] = ["id" => $cm->id, "name" => $cm->name];
            }
        }
        return $return;
    }
    public static function list_nuggets_parameters() {
        return new \external_function_parameters(
            array(
                'courseid' => new \external_value(PARAM_INT, 'The course id', VALUE_OPTIONAL, null),
                'query' => new \external_value(PARAM_TEXT, 'The text to search for', VALUE_OPTIONAL, null)
            ),
            '',
            VALUE_OPTIONAL
        );
    }
    public static function list_nuggets_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'id'    => new \external_value(PARAM_INT, 'ID of the context'),
                'name'  => new \external_value(PARAM_NOTAGS, 'The context name')
            ])
        );
    }

    public static function get_invitable_users(string $query, int $groupId, int $limit = 10) {
        global $DB, $USER, $OUTPUT, $PAGE;

        $PAGE->set_context(\context_system::instance());

        $params = self::validate_parameters(self::get_invitable_users_parameters(),
            [
                'query' => $query,
                'groupId' => $groupId,
            ]);

        $query = $params['query'];
        $groupId = $params['groupId'];

        $sl = "SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) as fullname
                FROM {user} u
                LEFT JOIN {lc_group_members} gm ON gm.userid = u.id AND gm.groupid = 11
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

        foreach ($users as $user) {
            $user->profilepicture = $OUTPUT->user_picture($user, ['size' => '240', 'link' => false, 'class' => '']);
        }

        return $users;
    }

    public static function get_invitable_users_parameters() {
        return new \external_function_parameters(
            [
                'query' => new \external_value(PARAM_TEXT, 'The text to search for', VALUE_REQUIRED, null),
                'groupId' => new \external_value(PARAM_INT, 'The group id', VALUE_REQUIRED, null),
                'limit' => new \external_value(PARAM_INT, 'The number of results to return', VALUE_OPTIONAL, 10)
            ],
            '',
            VALUE_REQUIRED
        );
    }

    public static function get_invitable_users_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'id'    => new \external_value(PARAM_INT, 'ID of the context'),
                'fullname'  => new \external_value(PARAM_NOTAGS, 'The context name'),
                'profilepicture' => new \external_value(PARAM_RAW, 'The user picture')
            ])
        );
    }

    public static function invite_user(int $userId, int $groupId) {
        $params = self::validate_parameters(self::invite_user_parameters(),
            [
                'userId' => $userId,
                'groupId' => $groupId,
            ]);

        $userId = $params['userId'];
        $groupId = $params['groupId'];

        $id = groups::invite_user_to_group($userId, $groupId);
        if ($id) {
            return ['errorcode' => 0];
        }
        return ['errorcode' => 1];
    }

    public static function invite_user_parameters() {
        return new \external_function_parameters(
            [
                'userId' => new \external_value(PARAM_INT, 'To userid of the user to invite', VALUE_REQUIRED, null),
                'groupId' => new \external_value(PARAM_INT, 'The group id', VALUE_REQUIRED, null),
            ],
            '',
            VALUE_REQUIRED
        );
    }

    public static function invite_user_returns() {
        new \external_single_structure([
            'errorcode' => new \external_value(PARAM_INT, 'Errorcode'),
        ]);
    }
}
