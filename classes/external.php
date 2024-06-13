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
namespace local_thi_learning_companions;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("$CFG->libdir/externallib.php");

/**
 * Collection of external methods to be called via JS. See services.php.
 */
class external extends \external_api {
    /**
     * lists learning nuggets (course activities) inside a course
     * @param int $courseid
     * @param string $query
     * @return array
     * @throws \invalid_parameter_exception
     */
    public static function list_nuggets($courseid, $query) {
        global $DB;
        $params = self::validate_parameters(self::list_nuggets_parameters(),
            [
                'courseid' => $courseid,
                'query' => $query,
            ]
        );
        $courseid = $params['courseid'];
        $query = $params['query'];

        if (is_null($courseid)) {
            return [];
        }
        $cminfo = \course_modinfo::instance($courseid);
        $return = [];
        $cms = $cminfo->get_cms();
        foreach ($cms as $cm) {
            if (empty($query) || stripos($cm->name, $query) > -1) {
                $return[] = ["id" => $cm->id, "name" => $cm->name];
            }
        }
        return $return;
    }

    /**
     * parameters for list nuggets
     * @return \external_function_parameters
     */
    public static function list_nuggets_parameters() {
        return new \external_function_parameters(
            [
                'courseid' => new \external_value(PARAM_INT, 'The course id', VALUE_OPTIONAL, null),
                'query' => new \external_value(PARAM_TEXT, 'The text to search for', VALUE_OPTIONAL, null),
            ],
            '',
            VALUE_OPTIONAL
        );
    }

    /**
     * return type for list_nuggets
     * @return \external_multiple_structure
     */
    public static function list_nuggets_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'id'    => new \external_value(PARAM_INT, 'ID of the context'),
                'name'  => new \external_value(PARAM_NOTAGS, 'The context name'),
            ])
        );
    }

    /**
     * returns users that can be invited
     * @param string $query
     * @param int $groupid
     * @param int $limit
     * @return array
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    public static function get_invitable_users(string $query, int $groupid, int $limit = 10) {
        global $DB, $USER, $OUTPUT, $PAGE;
        $PAGE->set_context(\context_system::instance());

        $params = self::validate_parameters(self::get_invitable_users_parameters(),
            [
                'query' => $query,
                'groupid' => $groupid,
            ]);

        $query = $params['query'];
        $groupid = $params['groupid'];

        $sl = "SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) as fullname
                FROM {user} u
                LEFT JOIN {thi_lc_group_members} gm ON gm.userid = u.id AND gm.groupid = :groupidjoin
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
            'groupidjoin' => $groupid,
        ];

        $users = $DB->get_records_sql($sl, $params, 0, $limit);

        foreach ($users as $user) {
            $user->profilepicture = $OUTPUT->user_picture($user, ['size' => '240', 'link' => false, 'class' => '']);
        }

        return $users;
    }

    /**
     * returns the expected paramters structure and types for the get_invitable_users method
     * @return \external_function_parameters
     */
    public static function get_invitable_users_parameters() {
        return new \external_function_parameters(
            [
                'query' => new \external_value(PARAM_TEXT, 'The text to search for', VALUE_REQUIRED, null),
                'groupid' => new \external_value(PARAM_INT, 'The group id', VALUE_REQUIRED, null),
            ],
            '',
            VALUE_REQUIRED
        );
    }

    /**
     * returns the return type structure for get_invitable_users
     * @return \external_multiple_structure
     */
    public static function get_invitable_users_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'id'    => new \external_value(PARAM_INT, 'ID of the context'),
                'fullname'  => new \external_value(PARAM_NOTAGS, 'The context name'),
                'profilepicture' => new \external_value(PARAM_RAW, 'The user picture'),
            ])
        );
    }

    /**
     * handles the inviting of users
     * @param int $userid
     * @param int $groupid
     * @return int[]
     * @throws \invalid_parameter_exception
     */
    public static function invite_user(int $userid, int $groupid) {
        $params = self::validate_parameters(self::invite_user_parameters(),
            [
                'userid' => $userid,
                'groupid' => $groupid,
            ]);

        $userid = $params['userId'];
        $groupid = $params['groupId'];

        $id = groups::invite_user_to_group($userid, $groupid);
        if ($id) {
            return ['errorcode' => 0];
        }
        return ['errorcode' => 1];
    }

    /**
     * returns the expected parameters for invite_users
     * @return \external_function_parameters
     */
    public static function invite_user_parameters() {
        return new \external_function_parameters(
            [
                'userid' => new \external_value(PARAM_INT, 'To userid of the user to invite', VALUE_REQUIRED, null),
                'groupid' => new \external_value(PARAM_INT, 'The group id', VALUE_REQUIRED, null),
            ],
            '',
            VALUE_REQUIRED
        );
    }

    /**
     * retursn the return type for invite_users
     * @return \external_single_structure
     */
    public static function invite_user_returns() {
        return new \external_single_structure([
            'errorcode' => new \external_value(PARAM_INT, 'Errorcode'),
        ]);
    }
}
