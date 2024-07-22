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

use local_thi_learning_companions\event\group_created;
use local_thi_learning_companions\event\group_deleted;
use local_thi_learning_companions\event\group_joined;
use local_thi_learning_companions\event\group_left;
use local_thi_learning_companions\event\group_updated;
use local_thi_learning_companions\forms\create_edit_group_form;

/**
 * Class with methods for handling groups.
 */
class groups {
    /**
     * chat type: Question to Mentor
     */
    const CHATTYPE_MENTOR = 0;
    /**
     * chat type: Group chat
     */
    const CHATTYPE_GROUP = 1;
    /**
     * join request: Created
     */
    const JOIN_REQUEST_CREATED = 0;
    /**
     * join request: Already requested
     */
    const JOIN_REQUEST_ALREADY_REQUESTED = 1;
    /**
     * join request: is already member
     */
    const JOIN_REQUEST_ALREADY_MEMBER = 2;
    /**
     * join request: failed
     */
    const JOIN_REQUEST_FAILED = 3;
    /**
     * join request: other error
     */
    const JOIN_REQUEST_OTHER_ERROR = 666;
    /**
     * Join: created
     */
    const JOIN_CREATED = 0;
    /**
     * Join: failed
     */
    const JOIN_FAILED = 3;

    /**
     * get all groups
     * @return group[]
     * @throws \dml_exception
     */
    public static function get_all_groups(): array {
        global $DB;

        $groups = $DB->get_records('local_thi_learning_companions_groups');
        $returngroups = [];
        foreach ($groups as $group) {
            $returngroups[] = new group($group->id);
        }
        return $returngroups;
    }

    /**
     * Returns a specific group
     * @param int $id
     *
     * @return group
     */
    public static function get_group_by_id(int $id): group {
        return new group($id);
    }

    /**
     * get group by chatid
     * @param int $chatid
     * @return group
     * @throws \dml_exception
     */
    public static function get_group_by_chatid(int $chatid): group {
        $groupid = self::get_groupid_of_chatid($chatid);
        $group = new group($groupid);
        return $group;
    }

    /**
     * get groupid of chatid
     * @param int $chatid
     * @return int
     * @throws \dml_exception
     */
    public static function get_groupid_of_chatid(int $chatid): int {
        global $DB;
        $groupid = $DB->get_field(
            'local_thi_learning_companions_chat',
            'relatedid',
            ['id' => $chatid, 'chattype' => self::CHATTYPE_GROUP]
        );
        return $groupid;
    }

    /**
     * get groups of user
     * @param int $userid
     * @param int|null $shouldincludegroupid
     * @param string $sortby possible values: latestcomment, earliestcomment, mylatestcomment, myearliestcomment
     * @return group[]
     * @throws \dml_exception
     */
    public static function get_groups_of_user($userid, int|null $shouldincludegroupid = null, $sortby = 'latestcomment') {
        global $DB, $CFG;

        $params = [$userid];
        $query = "SELECT g.id
                    FROM {local_thi_learning_companions_groups} g
                    JOIN {local_thi_learning_companions_group_members} gm ON gm.groupid = g.id AND gm.userid = ?";

        $groups = $DB->get_records_sql($query, $params);
        $return = [];

        foreach ($groups as $group) {
            $returngroup = new group($group->id, $userid); // ICTODO: check why this changes the PAGE context to context_system.
            $return[] = $returngroup;
        }

        switch($sortby) {
            case 'earliestcomment':
                usort($return, function($a, $b) {
                    if ($a->earliestcomment == $b->earliestcomment) {
                        return 0;
                    }
                    return ($a->earliestcomment < $b->earliestcomment) ? -1 : 1;
                });
                break;
            case 'latestcomment':
                usort($return, function($a, $b) {
                    if ($a->latestcomment == $b->latestcomment) {
                        return 0;
                    }
                    return ($a->latestcomment > $b->latestcomment) ? -1 : 1;
                });
                break;
            case 'myearliestcomment':
                usort($return, function($a, $b) {
                    if ($a->myearliestcomment == $b->myearliestcomment) {
                        return 0;
                    }
                    return ($a->myearliestcomment < $b->myearliestcomment) ? -1 : 1;
                });
                break;
            case 'mylatestcomment':
                usort($return, function($a, $b) {
                    if ($a->mylatestcomment == $b->mylatestcomment) {
                        return 0;
                    }
                    return ($a->mylatestcomment > $b->mylatestcomment) ? -1 : 1;
                });
                break;

            default:
                break;

        }

        $canseeallgroups = has_capability( 'tool/thi_learning_companions:group_manage', \context_system::instance());
        // Add preview group if it is set.
        $alreadyinarray = in_array($shouldincludegroupid, array_column($return, 'id'), false);
        if (!is_null($shouldincludegroupid) && !$alreadyinarray) {
            $shouldincludegroup = new group($shouldincludegroupid, $userid);

            if (!$shouldincludegroup->closedgroup || $canseeallgroups) {
                $shouldincludegroup->isPreviewGroup = true;
            } else {
                // Create a fake group, that does not hold any information.
                $shouldincludegroup = new \stdClass();
                $shouldincludegroup->isPreviewGroup = true;
                $shouldincludegroup->dummyGroup = true;
                $shouldincludegroup->userIsNotAMember = true;
                $shouldincludegroup->id = $shouldincludegroupid;
                $shouldincludegroup->imageurl = $CFG->wwwroot . '/local/thi_learning_companions/pix/group.svg';
                $shouldincludegroup->name = get_string('group_closed', 'local_thi_learning_companions');
            }
            $return = array_merge([$shouldincludegroup], $return);
        }

        return $return;
    }

    /**
     * get groups where user is admin
     * @param int|null $userid
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_groups_where_user_is_admin(int|null $userid = null) {
        global $USER, $DB;

        if ($userid === null) {
            $userid = $USER->id;
        }

        $query = "SELECT g.id
                    FROM {local_thi_learning_companions_groups} g
                    JOIN {local_thi_learning_companions_group_members} gm ON gm.groupid = g.id
                    WHERE gm.userid = ? AND gm.isadmin = 1";
        $params = [$userid];
        $groups = $DB->get_records_sql($query, $params);

        $return = [];
        foreach ($groups as $group) {
            $returngroup = new group($group->id, $USER->id);
            $return[] = $returngroup;
        }

        return $return;
    }

    /**
     * invite users to group
     * @param array $userids
     * @param int $groupid
     * @return void
     */
    public static function invite_users_to_group(array $userids, int $groupid) {
        $success = false;
        foreach ($userids as $userid) {
            $success = $success || self::invite_user_to_group($userid, $groupid);
        }
        return $success;
    }

    /**
     * invite user to group
     * @param int $userid
     * @param int $groupid
     * @return bool
     * @throws \dml_exception
     */
    public static function invite_user_to_group($userid, $groupid) {
        global $DB, $USER;

        $userisalreadyingroup = $DB->record_exists(
            'local_thi_learning_companions_group_members',
            ['userid' => $userid, 'groupid' => $groupid]
        );
        if ($userisalreadyingroup) {
            // Return for now. Maybe throw exception or something later.
            return false;
        }

        // Check if the current user is in the group.
        $userisingroup = $DB->record_exists(
            'local_thi_learning_companions_group_members',
            ['userid' => $USER->id, 'groupid' => $groupid]
        );
        if (!$userisingroup) {
            // Return for now. Maybe throw exception or something later.
            return false;
        }

        // If there is a request for joining this group, delete it.
        $DB->delete_records('local_thi_learning_companions_group_requests', ['userid' => $userid, 'groupid' => $groupid]);

        $id = self::group_add_member($groupid, $userid);

        if ($id) {
            messages::send_invited_to_group($userid, $groupid);
        }

        return $id;
    }

    /**
     * returns all available keywords
     * @return string[]
     * @throws \dml_exception
     */
    public static function get_all_keywords() {
        global $DB;
        return $DB->get_records_menu('local_thi_learning_companions_keywords');
    }

    /**
     * group create
     * @param \stdClass $data
     * @return int    id of created group
     * @throws \dml_exception
     * @throws \Exception
     */
    public static function group_create($data) {
        global $DB, $USER;
        // Check if the user has the permission to create a group for this course.
        $contextsystem = \context_system::instance();
        if (!empty($data->courseid)
            && !has_capability('local/thi_learning_companions:group_manage', $contextsystem)
        ) {
            $context = \context_course::instance($data->courseid);
            $isenrolled = is_enrolled($context);
            if (!$isenrolled) {
                throw new \moodle_exception(get_string('no_permission_to_create_course', 'local_thi_learning_companions'));
            }
        }
        // Check if there's already a group with that name by that user for that course.
        // Don't create groups that are indistinguishable from eachother.
        $similargroupexists = $DB->record_exists('local_thi_learning_companions_groups',
            ['courseid' => $data->courseid, 'createdby' => $USER->id, 'name' => $data->name]);
        if ($similargroupexists) {
            throw new \moodle_exception(get_string('no_group_duplicates_allowed', 'local_thi_learning_companions'));
        }

        $record = new \stdClass();
        $record->name = $data->name;
        if (is_array($data->description_editor)) {
            $record->description = $data->description_editor['text'];
        } else {
            $record->description = $data->description_editor;
        }
        $record->closedgroup = $data->closedgroup;
        $record->courseid = $data->courseid;
        $record->cmid = $data->cmid;
        $record->createdby = $USER->id;
        $record->timecreated = time();
        $record->timemodified = 0;

        $transaction = $DB->start_delegated_transaction();

        try {
            $groupid = $DB->insert_record('local_thi_learning_companions_groups', $record);
            $context = \context_system::instance();
            $options = [];
            $data = file_postupdate_standard_editor(
                $data,
                'description',
                $options,
                $context,
                'local_thi_learning_companions',
                'description',
                $groupid
            );
            $DB->set_field('local_thi_learning_companions_groups', 'description', $data->description, ['id' => $groupid]);
            self::save_group_image($groupid, $data->groupimage);
            self::group_assign_keywords($groupid, $data->keywords);
            self::create_group_chat($groupid);
            self::group_add_member($groupid, $USER->id, 1);
            $transaction->allow_commit();

            group_created::make($USER->id, $groupid, $data->keywords, (int)$record->courseid, (int)$record->cmid)->trigger();
            return $groupid;
        } catch (\Exception $e) {
            $transaction->rollback($e);
            throw new \Exception($e->getMessage(), $e->getCode()); // Pass the exception on to the top.
        }
    }

    /**
     * get keyword id
     * @param string $keyword
     * @return false|mixed
     * @throws \dml_exception
     */
    public static function keyword_get_id($keyword) {
        global $DB;
        return $DB->get_field('local_thi_learning_companions_keywords', 'id', ['keyword' => $keyword]);
    }

    /**
     * group update
     * @param int $groupid
     * @param string $name
     * @param string $description
     * @param int $closedgroup
     * @param mixed $keywords
     * @param int $courseid
     * @param int $cmid
     * @param mixed $image
     * @return void
     * @throws \dml_exception
     */
    public static function group_update($groupid, $name, $description, $closedgroup, $keywords, $courseid, $cmid, $image) {
        global $DB, $USER;
        // ICTODO: make sure that user may update this group.
        $group = $DB->get_record('local_thi_learning_companions_groups', ['id' => $groupid], '*', MUST_EXIST);
        $group->name = $name;
        $group->description = $description;
        $group->closedgroup = $closedgroup;
        $group->timemodified = time();
        $group->courseid = $courseid;
        $group->cmid = $cmid;
        $DB->update_record('local_thi_learning_companions_groups', $group);

        group_updated::make($USER->id, $groupid)->trigger();

        self::group_assign_keywords($groupid, $keywords);
        self::save_group_image($groupid, $image);
    }

    /**
     * save group image
     * @param int $groupid
     * @param mixed $image
     * @return void
     * @throws \dml_exception
     */
    protected static function save_group_image($groupid, $image) {
        $context = \context_system::instance();
        file_save_draft_area_files(
            $image,
            $context->id,
            'local_thi_learning_companions',
            'groupimage',
            $groupid,
            create_edit_group_form::get_filepickeroptions()
        );

    }

    /**
     * group add member
     * @param int $groupid
     * @param int $userid
     * @param int $isadmin
     *
     * @return bool
     * @throws \dml_exception
     */
    protected static function group_add_member(int $groupid, int $userid, $isadmin = 0) {
        global $DB;
        $record = new \stdClass();
        $record->groupid = $groupid;
        $record->userid = $userid;
        $record->isadmin = $isadmin;
        $record->joined = time();
        $isemptygroup = self::is_group_empty($groupid);
        $recordid = $DB->insert_record('local_thi_learning_companions_group_members', $record);
        if ($isemptygroup) {
            self::make_admin($userid, $groupid);
        }

        group_joined::make($userid, $groupid)->trigger();

        return $recordid;
    }

    /**
     * is group empty
     * @param int $groupid
     * @return bool
     * @throws \dml_exception
     */
    public static function is_group_empty(int $groupid) {
        global $DB;
        return !$DB->record_exists_sql("SELECT gm.*
                FROM {local_thi_learning_companions_group_members} gm
                JOIN {user} u ON gm.userid = u.id
                    AND u.deleted = 0
                WHERE gm.groupid = ?",
            [$groupid]
        );
    }

    /**
     * assign keywords to group
     * @param int $groupid
     * @param array $keywords
     * @return void
     * @throws \dml_exception
     */
    protected static function group_assign_keywords($groupid, $keywords) {
        self::group_remove_all_keywords($groupid); // Start with a blank slate.
        foreach ($keywords as $keyword) {
            self::group_assign_keyword($groupid, $keyword);
        }
    }

    /**
     * assign keyword to group
     * @param int $groupid
     * @param string $keyword
     * @return void
     * @throws \dml_exception
     */
    protected static function group_assign_keyword($groupid, $keyword) {
        global $DB;
        $keywordid = self::keyword_get_id($keyword);
        if (!$keywordid) {
            $keywordid = self::keyword_create($keyword);
            // ICTODO: handle errors, like when the keyword is too long.
        }
        $obj = new \stdClass();
        $obj->groupid = $groupid;
        $obj->keywordid = $keywordid;
        $DB->insert_record('local_thi_learning_companions_groups_keywords', $obj);
    }

    /**
     * create keyword
     * @param string $keyword
     * @return bool|int|mixed
     * @throws \dml_exception
     */
    protected static function keyword_create($keyword) {
        global $DB;
        if ($DB->record_exists('local_thi_learning_companions_keywords', ['keyword' => $keyword])) {
            return $DB->get_field('local_thi_learning_companions_keywords', 'id', ['keyword' => $keyword]);
        }
        $obj = new \stdClass();
        $obj->keyword = $keyword;
        return $DB->insert_record('local_thi_learning_companions_keywords', $obj);
    }

    /**
     * group remove all keywords
     * @param int $groupid
     * @return void
     * @throws \dml_exception
     */
    protected static function group_remove_all_keywords($groupid) {
        global $DB;
        $DB->delete_records('local_thi_learning_companions_groups_keywords', ['groupid' => $groupid]);
    }

    /**
     * creates a chat record for the group
     * @param int $groupid
     * @return void
     * @throws \dml_exception
     */
    protected static function create_group_chat($groupid) {
        global $DB;
        $record = new \stdClass();
        $record->chattype = self::CHATTYPE_GROUP;
        $record->relatedid = $groupid;
        $record->timecreated = time();
        $record->course = $DB->get_field(
            'local_thi_learning_companions_groups',
            'courseid',
            ['id' => $groupid]
        );
        if ($DB->record_exists(
            'local_thi_learning_companions_chat',
            ['chattype' => $record->chattype, 'relatedid' => $record->relatedid]
        )) {
            return; // Already exists (for some reason) - nothing to do.
        }
        $DB->insert_record('local_thi_learning_companions_chat', $record);
    }

    /**
     * leave group
     * @param int $userid The id of the user who will leave the group
     * @param int $groupid The id of the group the user will leave
     *
     * @return bool
     * @throws \dml_exception
     */
    public static function leave_group(int $userid, int $groupid) {
        global $DB;
        $deleted = $DB->delete_records('local_thi_learning_companions_group_members', ['groupid' => $groupid, 'userid' => $userid]);
        group_left::make($userid, $groupid)->trigger();

        $group = new group($groupid);

        // If the group is a closed group, and the user was the last member, delete the group.
        if ($group->closedgroup && $group->membercount === 0) {
            self::delete_group($groupid);
            return true;
        }

        return $deleted;
    }

    /**
     * make admin
     * @param int $userid
     * @param int $groupid
     * @return void
     * @throws \dml_exception
     */
    public static function make_admin(int $userid, int $groupid) {
        global $DB, $USER;
        $DB->set_field('local_thi_learning_companions_group_members', 'isadmin', 1, ['groupid' => $groupid, 'userid' => $userid]);

        messages::send_appointed_to_admin_notification($userid, $groupid, $USER->id);
    }

    /**
     * unmake admin
     * @param int $userid
     * @param int $groupid
     * @return void
     * @throws \dml_exception
     */
    public static function unmake_admin(int $userid, int $groupid) {
        global $DB;
        $DB->set_field('local_thi_learning_companions_group_members', 'isadmin', 0, ['groupid' => $groupid, 'userid' => $userid]);
    }

    /**
     * request group join
     * @param int $userid
     * @param int $groupid
     *
     * @return int
     * @throws \dml_exception
     */
    public static function request_group_join($userid, $groupid): int {
        // Check if the group is closed.
        $group = new group($groupid);

        // If the user is already a member, don't do anything.
        if ($group->is_user_member($userid)) {
            return self::JOIN_REQUEST_ALREADY_MEMBER;
        }

        // If the group is not closed, there is no need to request to join. Just join.
        if (!$group->closedgroup) {
            return self::group_add_member($groupid, $userid);
        }

        // If the user has already requested to join, don't do anything.
        if (self::join_is_requested($userid, $groupid)) {
            return self::JOIN_REQUEST_ALREADY_REQUESTED;
        }

        $inserted = self::add_group_join_request($groupid, $userid);

        if ($inserted) {
            foreach ($group->admins as $admin) {
                messages::send_group_join_requested_notification($userid, $admin->id, $groupid);
            }
        }

        return $inserted ? self::JOIN_REQUEST_CREATED : self::JOIN_REQUEST_FAILED;
    }

    /**
     * join is requested
     * @param int $userid
     * @param int $groupid
     * @return bool
     * @throws \dml_exception
     */
    public static function join_is_requested(int $userid, int $groupid) {
        global $DB;
        return $DB->record_exists('local_thi_learning_companions_group_requests', ['groupid' => $groupid, 'userid' => $userid]);
    }

    /**
     * get group join requests
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public static function get_group_join_requests(): array {
        global $DB;

        $groups = self::get_groups_where_user_is_admin();
        $groupids = array_map(static function($group) {
            return $group->id;
        }, $groups);

        if (empty($groupids)) {
            return [];
        }

        $requests = $DB->get_records_sql('SELECT * FROM {local_thi_learning_companions_group_requests} WHERE groupid IN (' .
                implode(',', $groupids) . ') and denied = 0') ?? [];
        $requestedusersids = array_map(static function($request) {
            return $request->userid;
        }, $requests);
        $requestedusers = $DB->get_records_list('user', 'id', $requestedusersids) ?? [];

        foreach ($requests as $request) {
            $request->user = $requestedusers[$request->userid];
        }

        return $requests;
    }

    /**
     * add group join request
     * @param int $groupid
     * @param int $userid
     * @return bool|int
     * @throws \dml_exception
     */
    protected static function add_group_join_request($groupid, $userid) {
        global $DB;
        $record = new \stdClass();
        $record->groupid = $groupid;
        $record->userid = $userid;
        $record->timecreated = time();
        return $DB->insert_record('local_thi_learning_companions_group_requests', $record);
    }

    /**
     * accept group join request
     * @param int $requestid
     * @return void
     * @throws \dml_exception
     */
    public static function accept_group_join_request($requestid) {
        global $DB;

        // Get the request.
        $request = $DB->get_record('local_thi_learning_companions_group_requests', ['id' => $requestid]);
        // Add the user to the group.
        self::group_add_member($request->groupid, $request->userid);
        // Delete the request.
        $DB->delete_records('local_thi_learning_companions_group_requests', ['id' => $requestid]);
        messages::send_group_join_accepted_notification($request->userid, $request->groupid);
    }

    /**
     * deny group join request
     * @param int $requestid
     * @return void
     * @throws \dml_exception
     */
    public static function deny_group_join_request($requestid) {
        global $DB;
        $request = $DB->get_record('local_thi_learning_companions_group_requests', ['id' => $requestid]);

        $DB->set_field('local_thi_learning_companions_group_requests', 'denied', 1, ['id' => $requestid]);
        messages::send_group_join_denied_notification($request->userid, $request->groupid);
    }

    /**
     * join group
     * @param int $userid
     * @param int $groupid
     *
     * @return int
     * @throws \dml_exception
     */
    public static function join_group(int $userid, int $groupid): int {
        // Check if the group is open.
        $group = new group($groupid);

        // If the group is not open, there is no need to join. Just request to join.
        if ($group->closedgroup) {
            return self::request_group_join($userid, $groupid);
        }

        $inserted = self::group_add_member($groupid, $userid);
        return $inserted ? self::JOIN_CREATED : self::JOIN_FAILED;
    }

    /**
     * delete group
     * @param int $groupid
     * @return void
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     */
    public static function delete_group(int $groupid) {
        global $DB, $USER;

        $event = group_deleted::make($USER->id, $groupid);
        $event->add_record_snapshot('local_thi_learning_companions_groups',
            $DB->get_record('local_thi_learning_companions_groups',
                ['id' => $groupid]
            )
        );
        $event->add_record_snapshot('local_thi_learning_companions_chat',
            $DB->get_record('local_thi_learning_companions_chat',
                ['chattype' => self::CHATTYPE_GROUP, 'relatedid' => $groupid]
            )
        );
        $groupmembers = $DB->get_records('local_thi_learning_companions_group_members',
            ['groupid' => $groupid]);
        foreach ($groupmembers as $groupmember) {
            $event->add_record_snapshot('local_thi_learning_companions_group_members', $groupmember);
        }
        $event->trigger();

        $transaction = $DB->start_delegated_transaction();
        // Get Chat ID.
        $chatid = $DB->get_field('local_thi_learning_companions_chat',
            'id',
            ['chattype' => self::CHATTYPE_GROUP, 'relatedid' => $groupid]
        );
        // Delete file attachments.
        self::delete_attachments_of_chat($chatid);
        // Delete the group members.
        $DB->delete_records('local_thi_learning_companions_group_members',
            ['groupid' => $groupid]);
        // Delete the group requests.
        $DB->delete_records('local_thi_learning_companions_group_requests',
            ['groupid' => $groupid]);
        // Delete the group keywords.
        $DB->delete_records('local_thi_learning_companions_groups_keywords',
            ['groupid' => $groupid]);
        // Delete the group.
        $DB->delete_records('local_thi_learning_companions_groups',
            ['id' => $groupid]);
        // Delete Chat.
        $DB->delete_records('local_thi_learning_companions_chat',
            ['id' => $chatid]);
        // Delete Chat Messages.
        $DB->delete_records('local_thi_learning_companions_chat_comment',
            ['chatid' => $chatid]);
        $transaction->allow_commit();
    }

    /**
     * deletes all the files that were uploaded to comments of a chat
     * @param int $chatid
     * @return void
     * @throws \dml_exception
     */
    protected static function delete_attachments_of_chat($chatid) {
        global $DB;
        // Delete Chat Messages.
        $comments = $DB->get_records('local_thi_learning_companions_chat_comment',
            ['chatid' => $chatid]);
        $fs = new \file_storage();
        $context = \context_system::instance();
        foreach ($comments as $comment) {
            foreach ($comment->attachments as $attachment) {
                $fs->delete_area_files($context->id,
                    'local_thi_learning_companions',
                    'message',
                    $comment->id);
                $fs->delete_area_files($context->id,
                    'local_thi_learning_companions',
                    'attachments',
                    $comment->id);
            }
        }
    }

    /**
     * count comments since last visit
     * @param int $groupid
     * @return int
     * @throws \dml_exception
     */
    public static function count_comments_since_last_visit($groupid) {
        global $DB, $USER;
        $chatid = $DB->get_field('local_thi_learning_companions_chat',
            'id',
            ['chattype' => self::CHATTYPE_GROUP, 'relatedid' => $groupid]);
        $lastvisited = $DB->get_field('local_thi_learning_companions_chat_lastvisited',
            'timevisited',
            ['chatid' => $chatid, 'userid' => $USER->id]);
        if (false === $lastvisited) {
            $lastvisited = 0;
        }
        $commentssincelastvisit = $DB->get_records_sql(
            'SELECT * FROM {local_thi_learning_companions_chat_comment}
                    WHERE chatid = ? AND timecreated > ?',
            [$chatid, $lastvisited]
        );
        if (false === $commentssincelastvisit) {
            return 0;
        }
        return count($commentssincelastvisit);
    }

    /**
     * * is the user a group member
     * @param int $userid
     * @param int $groupid
     * @return bool
     * @throws \dml_exception
     */
    public static function is_group_member(int $userid, int $groupid) {
        global $DB;
        return $DB->record_exists('local_thi_learning_companions_group_members',
            ['userid' => $userid, 'groupid' => $groupid]);
    }

    /**
     * may user view the group
     * @param int $groupid
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function may_view_group(int $groupid) {
        global $USER;
        $isgroupmember = self::is_group_member($USER->id, $groupid);
        $context = \context_system::instance();
        $maymanagegroups = has_capability(
            'local/thi_learning_companions:group_manage',
            $context
        );
        return $isgroupmember || $maymanagegroups;
    }
}
