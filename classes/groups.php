<?php

namespace local_thi_learning_companions;

use local_thi_learning_companions\event\group_created;
use local_thi_learning_companions\event\group_deleted;
use local_thi_learning_companions\event\group_joined;
use local_thi_learning_companions\event\group_left;
use local_thi_learning_companions\event\group_updated;
use local_thi_learning_companions\forms\create_edit_group_form;

class groups {
    const CHATTYPE_MENTOR = 0;
    const CHATTYPE_GROUP = 1;

    const JOIN_REQUEST_CREATED = 0;
    const JOIN_REQUEST_ALREADY_REQUESTED = 1;
    const JOIN_REQUEST_ALREADY_MEMBER = 2;
    const JOIN_REQUEST_FAILED = 3;
    const JOIN_REQUEST_OTHER_ERROR = 666;

    const JOIN_CREATED = 0;
    const JOIN_FAILED = 3;

    /**
     * @return group[]
     * @throws \dml_exception
     */
    public static function get_all_groups(): array {
        global $DB;

        $groups = $DB->get_records('thi_lc_groups');
        $returnGroups = array();
        foreach($groups as $group) {
            $returnGroups[] = new group($group->id);
        }
        return $returnGroups;
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
     * @param int $chatid
     * @return int
     * @throws \dml_exception
     */
    public static function get_groupid_of_chatid(int $chatid): int {
        global $DB;
        $groupid = $DB->get_field('thi_lc_chat', 'relatedid', array('id' => $chatid, 'chattype' => self::CHATTYPE_GROUP));
        return $groupid;
    }

    /**
     * @param int $userid
     * @param string $sortby possible values: latestcomment, earliestcomment, mylatestcomment, myearliestcomment
     * @return group[]
     * @throws \dml_exception
     */
    public static function get_groups_of_user($userid, int $shouldIncludeGroupId = null, $sortby = 'latestcomment') {
        global $DB, $CFG;

        $params = [$userid];
        $query = "SELECT g.id
                    FROM {thi_lc_groups} g
                    JOIN {thi_lc_group_members} gm ON gm.groupid = g.id AND gm.userid = ?";

        $groups = $DB->get_records_sql($query, $params);
        $return = [];

        foreach($groups as $group) {
            $returnGroup = new group($group->id, $userid); // ICTODO: check why this changes the PAGE context to context_system
            $return[] = $returnGroup;
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

        $canSeeAllGroups = has_capability( 'tool/thi_learning_companions:group_manage', \context_system::instance());
        //Add preview group if it is set
        $alreadyInArray = in_array($shouldIncludeGroupId, array_column($return, 'id'), false);
        if(!is_null($shouldIncludeGroupId) && !$alreadyInArray) {
            $shouldIncludeGroup = new group($shouldIncludeGroupId, $userid);

            if (!$shouldIncludeGroup->closedgroup || $canSeeAllGroups) {
                $shouldIncludeGroup->isPreviewGroup = true;
            } else {
                //Create a fake group, that does not hold any information
                $shouldIncludeGroup = new \stdClass();
                $shouldIncludeGroup->isPreviewGroup = true;
                $shouldIncludeGroup->dummyGroup = true;
                $shouldIncludeGroup->userIsNotAMember = true;
                $shouldIncludeGroup->id = $shouldIncludeGroupId;
                $shouldIncludeGroup->imageurl = $CFG->wwwroot . '/local/thi_learning_companions/pix/group.svg';
                $shouldIncludeGroup->name = get_string('group_closed', 'local_thi_learning_companions');
            }
            $return = array_merge([$shouldIncludeGroup], $return);
        }

        return $return;
    }

    public static function get_groups_where_user_is_admin(int $userId = null) {
        global $USER, $DB;

        if ($userId === null) {
            $userId = $USER->id;
        }

        $query = "SELECT g.id
                    FROM {thi_lc_groups} g
                    JOIN {thi_lc_group_members} gm ON gm.groupid = g.id
                    WHERE gm.userid = ? AND gm.isadmin = 1";
        $params = [$userId];
        $groups = $DB->get_records_sql($query, $params);

        $return = [];
        foreach($groups as $group) {
            $returnGroup = new group($group->id, $USER->id);
            $return[] = $returnGroup;
        }

        return $return;
    }

    /**
     * @param array $userids
     * @param int $groupid
     * @return void
     */
    public static function invite_users_to_group(array $userids, int $groupid) {
        $success = false;
        foreach($userids as $userid) {
            $success = $success || self::invite_user_to_group($userid, $groupid);
        }
        return $success;
    }

    public static function invite_user_to_group($userid, $groupid) {
        global $DB, $USER;

        $userIsAlreadyInGroup = $DB->record_exists('thi_lc_group_members', ['userid' => $userid, 'groupid' => $groupid]);
        if ($userIsAlreadyInGroup) {
            // Return for now. Maybe throw exception or something later
            return false;
        }

        //Check if the current user is in the group
        $userIsInGroup = $DB->record_exists('thi_lc_group_members', ['userid' => $USER->id, 'groupid' => $groupid]);
        if (!$userIsInGroup) {
            // Return for now. Maybe throw exception or something later
            return false;
        }

        //If there is a request for joining this group, delete it
        $DB->delete_records('thi_lc_group_requests', ['userid' => $userid, 'groupid' => $groupid]);

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
        return $DB->get_records_menu('thi_lc_keywords');
    }

    /**
     * @param $data
     * @return int    id of created group
     * @throws \dml_exception
     * @throws \Exception
     */
    public static function group_create($data) {
        global $DB, $USER;
        // ICTODO: check if the user has the permission to create a group for this course
        // ICTODO: check if there's already a group with that name by that user for that course - don't create groups that are indistinguishable from eachother
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
            $groupid = $DB->insert_record('thi_lc_groups', $record);
            $context = \context_system::instance();
            $options = [];
            $data = file_postupdate_standard_editor($data, 'description', $options, $context, 'local_thi_learning_companions', 'description', $groupid);
            $DB->set_field('thi_lc_groups', 'description', $data->description, array('id' => $groupid));
            self::save_group_image($groupid, $data->groupimage);
            self::group_assign_keywords($groupid, $data->keywords);
            self::group_add_member($groupid, $USER->id, 1);
            self::create_group_chat($groupid);
            $transaction->allow_commit();

            group_created::make($USER->id, $groupid, $data->keywords, $record->courseid, $record->cmid)->trigger();
            return $groupid;
        } catch (\Exception $e) {
            $transaction->rollback($e);
            throw new \Exception($e->getMessage(), $e->getCode()); // pass the exception on to the top
        }
    }

    /**
     * @param $keyword
     * @return false|mixed
     * @throws \dml_exception
     */
    public static function keyword_get_id($keyword) {
        global $DB;
        return $DB->get_field('thi_lc_keywords', 'id', ['keyword' => $keyword]);
    }

    /**
     * @param $groupid
     * @param $name
     * @param $description
     * @param $closedgroup
     * @param $keywords
     * @return void
     * @throws \dml_exception
     */
    public static function group_update($groupid, $name, $description, $closedgroup, $keywords, $courseid, $cmid, $image) {
        global $DB, $USER;
        // ICTODO: make sure that user may update this group
        $group = $DB->get_record('thi_lc_groups', ['id' => $groupid], '*', MUST_EXIST);
        $group->name = $name;
        $group->description = $description;
        $group->closedgroup = $closedgroup;
        $group->timemodified = time();
        $group->courseid = $courseid;
        $group->cmid = $cmid;
        $DB->update_record('thi_lc_groups', $group);

        group_updated::make($USER->id, $groupid)->trigger();

        self::group_assign_keywords($groupid, $keywords);
        self::save_group_image($groupid, $image);
    }

    /**
     * @return string[]
     */
    public static function get_available_topics() {
        // ICTODO: this is just a generic placeholder. Find out where we can get topics from
        // ICTODO: get topics relevant to the student - maybe from their profile fields or course categories for their enrolled courses
        return array('IT', 'Mathematik', 'Maschinenbau');
    }

    /**
     * @param $groupid
     * @param $image
     * @return void
     * @throws \dml_exception
     */
    protected static function save_group_image($groupid, $image) {
        $context = \context_system::instance();
        file_save_draft_area_files($image, $context->id, 'local_thi_learning_companions', 'groupimage', $groupid, create_edit_group_form::get_filepickeroptions());

    }

    /**
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
        $isEmptyGroup = self::is_group_empty($groupid);
        $recordID = $DB->insert_record('thi_lc_group_members', $record);
        if ($isEmptyGroup) {
            self::make_admin($userid, $groupid);
        }

        group_joined::make($userid, $groupid)->trigger();

        return $recordID;
    }

    /**
     * @param int $groupid
     * @return bool
     * @throws \dml_exception
     */
    public static function is_group_empty(int $groupid) {
        global $DB;
        return !$DB->record_exists_sql("SELECT gm.* 
                FROM {thi_lc_group_members} gm
                JOIN {user} u ON gm.userid = u.id
                    AND u.deleted = 0
                WHERE gm.groupid = ?",
            array($groupid)
        );
    }

    /**
     * @param $groupid
     * @param $keywords
     * @return void
     * @throws \dml_exception
     */
    protected static function group_assign_keywords($groupid, $keywords) {
        self::group_remove_all_keywords($groupid); // start with a blank slate
        foreach ($keywords as $keyword) {
            self::group_assign_keyword($groupid, $keyword);
        }
    }

    /**
     * @param $groupid
     * @param $keyword
     * @return void
     * @throws \dml_exception
     */
    protected static function group_assign_keyword($groupid, $keyword) {
        global $DB;
        $keywordID = self::keyword_get_id($keyword);
        if (!$keywordID) {
            $keywordID = self::keyword_create($keyword);
            // ICTODO: handle errors, like when the keyword is too long
        }
        $obj = new \stdClass();
        $obj->groupid = $groupid;
        $obj->keywordid = $keywordID;
        $DB->insert_record('thi_lc_groups_keywords', $obj);
    }


    /**
     * @param $keyword
     * @return bool|int|mixed
     * @throws \dml_exception
     */
    protected static function keyword_create($keyword) {
        global $DB;
        if ($DB->record_exists('thi_lc_keywords', array('keyword' => $keyword))) {
            return $DB->get_field('thi_lc_keywords', 'id', array('keyword' => $keyword));
        }
        $obj = new \stdClass();
        $obj->keyword = $keyword;
        return $DB->insert_record('thi_lc_keywords', $obj);
    }


    /**
     * @param $groupid
     * @return void
     * @throws \dml_exception
     */
    protected static function group_remove_all_keywords($groupid) {
        global $DB;
        $DB->delete_records('thi_lc_groups_keywords', array('groupid' => $groupid));
    }

    /**
     * creates a chat record for the group
     * @param $groupid
     * @return void
     * @throws \dml_exception
     */
    protected static function create_group_chat($groupid) {
        global $DB;
        $record = new \stdClass();
        $record->chattype = self::CHATTYPE_GROUP;
        $record->relatedid = $groupid;
        $record->timecreated = time();
        $record->course = $DB->get_field('thi_lc_groups', 'courseid', array('id' => $groupid));
        $DB->insert_record('thi_lc_chat', $record);
    }

    /**
     * @param int $userId The id of the user who will leave the group
     * @param int $groupid The id of the group the user will leave
     *
     * @return bool
     * @throws \dml_exception
     */
    public static function leave_group(int $userId, int $groupId) {
        global $DB;
        $deleted = $DB->delete_records('thi_lc_group_members', ['groupid' => $groupId, 'userid' => $userId]);
        group_left::make($userId, $groupId)->trigger();

        $group = new group($groupId);

        // If the group is a closed group, and the user was the last member, delete the group
        if ($group->closedgroup && $group->membercount === 0) {
            self::delete_group($groupId);
            return true;
        }

        return $deleted;
    }

    public static function make_admin(int $userId, int $groupId) {
        global $DB, $USER;
        $DB->set_field('thi_lc_group_members', 'isadmin', 1, ['groupid' => $groupId, 'userid' => $userId]);

        messages::send_appointed_to_admin_notification($userId, $groupId, $USER->id);
    }

    public static function unmake_admin(int $userId, int $groupId) {
        global $DB;
        $DB->set_field('thi_lc_group_members', 'isadmin', 0, ['groupid' => $groupId, 'userid' => $userId]);
    }

    /**
     * @param $userId
     * @param $groupId
     *
     * @return int
     * @throws \dml_exception
     */
    public static function request_group_join($userId, $groupId): int {
        // Check if the group is closed
        $group = new group($groupId);

        // If the user is already a member, don't do anything
        if ($group->is_user_member($userId)) {
            return self::JOIN_REQUEST_ALREADY_MEMBER;
        }

        // If the group is not closed, there is no need to request to join. Just join
        if (!$group->closedgroup) {
            return self::group_add_member($groupId, $userId);
        }

        // If the user has already requested to join, don't do anything
        if (self::join_is_requested($userId, $groupId)) {
            return self::JOIN_REQUEST_ALREADY_REQUESTED;
        }

        $inserted = self::add_group_join_request($groupId, $userId);

        if ($inserted) {
            foreach ($group->admins as $admin) {
                messages::send_group_join_requested_notification($userId, $admin->id, $groupId);
            }
        }

        return $inserted ? self::JOIN_REQUEST_CREATED : self::JOIN_REQUEST_FAILED;
    }

    public static function join_is_requested(int $userId, int $groupId) {
        global $DB;
        return $DB->record_exists('thi_lc_group_requests', ['groupid' => $groupId, 'userid' => $userId]);
    }

    /**
     * @return \stdClass[]
     * @throws \dml_exception
     */
    public static function get_group_join_requests(): array {
        global $DB;

        $groups = self::get_groups_where_user_is_admin();
        $groupIds = array_map(static function($group) {
            return $group->id;
        }, $groups);

        if (empty($groupIds)) {
            return [];
        }

        $requests = $DB->get_records_sql('SELECT * FROM {thi_lc_group_requests} WHERE groupid IN (' . implode(',', $groupIds) . ') and denied = 0') ?? [];
        $requestedUsersIds = array_map(static function($request) {
            return $request->userid;
        }, $requests);
        $requestedUsers = $DB->get_records_list('user', 'id', $requestedUsersIds) ?? [];

        foreach ($requests as $request) {
            $request->user = $requestedUsers[$request->userid];
        }

        return $requests;
    }

    /**
     * @param $groupId
     * @param $userId
     * @return bool|int
     * @throws \dml_exception
     */
    protected static function add_group_join_request($groupId, $userId) {
        global $DB;
        $record = new \stdClass();
        $record->groupid = $groupId;
        $record->userid = $userId;
        $record->timecreated = time();
        return $DB->insert_record('thi_lc_group_requests', $record);
    }

    /**
     * @param $requestId
     * @return void
     * @throws \dml_exception
     */
    public static function accept_group_join_request($requestId) {
        global $DB;

        //Get the request
        $request = $DB->get_record('thi_lc_group_requests', ['id' => $requestId]);
        //Add the user to the group
        self::group_add_member($request->groupid, $request->userid);
        //Delete the request
        $DB->delete_records('thi_lc_group_requests', ['id' => $requestId]);
        messages::send_group_join_accepted_notification($request->userid, $request->groupid);
    }

    /**
     * @param $requestId
     * @return void
     * @throws \dml_exception
     */
    public static function deny_group_join_request($requestId) {
        global $DB;
        $request = $DB->get_record('thi_lc_group_requests', ['id' => $requestId]);

        $DB->set_field('thi_lc_group_requests', 'denied', 1, ['id' => $requestId]);
        messages::send_group_join_denied_notification($request->userid, $request->groupid);
    }

    /**
     * @param int $userId
     * @param int $groupId
     *
     * @return int
     * @throws \dml_exception
     */
    public static function join_group(int $userId, int $groupId): int {
        // Check if the group is open
        $group = new group($groupId);

        // If the group is not open, there is no need to join. Just request to join
        if ($group->closedgroup) {
            return self::request_group_join($userId, $groupId);
        }

        $inserted = self::group_add_member($groupId, $userId);
        return $inserted ? self::JOIN_CREATED : self::JOIN_FAILED;
    }

    /**
     * @param int $groupId
     * @return void
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     */
    public static function delete_group(int $groupId) {
        global $DB, $USER;

        $event = group_deleted::make($USER->id, $groupId);
        $event->add_record_snapshot('thi_lc_groups', $DB->get_record('thi_lc_groups', ['id' => $groupId]));
        $event->add_record_snapshot('thi_lc_chat', $DB->get_record('thi_lc_chat', ['chattype' => self::CHATTYPE_GROUP, 'relatedid' => $groupId]));
        $groupMembers = $DB->get_records('thi_lc_group_members', ['groupid' => $groupId]);
        foreach ($groupMembers as $groupMember) {
            $event->add_record_snapshot('thi_lc_group_members', $groupMember);
        }
        $event->trigger();

        $transaction = $DB->start_delegated_transaction();
        //Get Chat ID
        $chatId = $DB->get_field('thi_lc_chat', 'id', ['chattype' => self::CHATTYPE_GROUP, 'relatedid' => $groupId]);
        // Delete file attachments
        self::delete_attachments_of_chat($chatId);
        // Delete the group members
        $DB->delete_records('thi_lc_group_members', ['groupid' => $groupId]);
        // Delete the group requests
        $DB->delete_records('thi_lc_group_requests', ['groupid' => $groupId]);
        // Delete the group keywords
        $DB->delete_records('thi_lc_groups_keywords', ['groupid' => $groupId]);
        // Delete the group
        $DB->delete_records('thi_lc_groups', ['id' => $groupId]);
        //Delete Chat
        $DB->delete_records('thi_lc_chat', ['id' => $chatId]);
        //Delete Chat Messages
        $DB->delete_records('thi_lc_chat_comment', ['chatid' => $chatId]);
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
        //Delete Chat Messages
        $comments = $DB->get_records('thi_lc_chat_comment', ['chatid' => $chatid]);
        $fs = new \file_storage();
        $context = \context_system::instance();
        foreach($comments as $comment) {
            foreach($comment->attachments as $attachment) {
                $fs->delete_area_files($context->id,'local_thi_learning_companions', 'message', $comment->id);
                $fs->delete_area_files($context->id,'local_thi_learning_companions', 'attachments', $comment->id);
            }
        }
    }

    /**
     * @param $groupid
     * @return int
     * @throws \dml_exception
     */
    public static function count_comments_since_last_visit($groupid) {
        global $DB, $USER;
        $chatId = $DB->get_field('thi_lc_chat', 'id', ['chattype' => self::CHATTYPE_GROUP, 'relatedid' => $groupid]);
        $lastVisited = $DB->get_field('thi_lc_chat_lastvisited', 'timevisited', array('chatid' => $chatId, 'userid' => $USER->id));
        if (false === $lastVisited) {
            $lastVisited = 0;
        }
        $commentsSinceLastVisit = $DB->get_records_sql(
            'SELECT * FROM {thi_lc_chat_comment}
                    WHERE chatid = ? AND timecreated > ?',
            array($chatId, $lastVisited)
        );
        if (false === $commentsSinceLastVisit) {
            return 0;
        }
        return count($commentsSinceLastVisit);
    }

    /**
     * @param int $userid
     * @param int $groupid
     * @return bool
     * @throws \dml_exception
     */
    public static function is_group_member(int $userid, int $groupid) {
        global $DB;
        return $DB->record_exists('thi_lc_group_members', array('userid' => $userid, 'groupid' => $groupid));
    }

    /**
     * @param int $groupid
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function may_view_group(int $groupid) {
        global $USER;
        $isGroupMember = self::is_group_member($USER->id, $groupid);
        $context = \context_system::instance();
        $mayManageGroups = has_capability('local/thi_learning_companions:group_manage', $context);
        return $isGroupMember || $mayManageGroups;
    }
}
