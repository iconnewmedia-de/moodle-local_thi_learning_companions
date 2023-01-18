<?php

namespace local_learningcompanions;

use local_learningcompanions\forms\create_edit_group_form;

class groups {
    const CHATTYPE_MENTOR = 0;
    const CHATTYPE_GROUP = 1;

    /**
     * @return group[]
     * @throws \dml_exception
     */
    public static function get_all_groups(): array {
        global $DB;

        $groups = $DB->get_records('lc_groups');
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
     * @param int $userid
     * @param string $sortby possible values: latestcomment, earliestcomment, mylatestcomment, myearliestcomment
     * @return group[]
     * @throws \dml_exception
     */
    public static function get_groups_of_user($userid, int $previewGroup = null, $sortby = 'latestcomment') {
        global $DB;

        $params = array($userid);
        $query = "SELECT g.id
                    FROM {lc_groups} g
                    JOIN {lc_group_members} gm ON gm.groupid = g.id AND gm.userid = ?";

        $groups = $DB->get_records_sql($query, $params);
        $return = [];

        foreach($groups as $group) {
            $returnGroup = new group($group->id, $userid);
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

        //Add preview group if it is set
        if(is_null($previewGroup) === false && !in_array($previewGroup, array_column($return, 'id'), true)) {
            $previewGroup = new group($previewGroup, $userid);
            if(!$previewGroup->closedgroup) {
                $previewGroup->isPreviewGroup = true;
                $return = array_merge([$previewGroup], $return);
            }
        }

        return $return;
    }

    public static function get_groups_where_user_is_admin(int $userId = null) {
        global $USER, $DB;

        if ($userId === null) {
            $userId = $USER->id;
        }

        $query = "SELECT g.id
                    FROM {lc_groups} g
                    JOIN {lc_group_members} gm ON gm.groupid = g.id
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

    public static function invite_user_to_group($userid, $groupid) {
        global $DB, $USER;

        $userIsAlreadyInGroup = $DB->record_exists('lc_group_members', ['userid' => $userid, 'groupid' => $groupid]);
        if ($userIsAlreadyInGroup) {
            // Return for now. Maybe throw exception or something later
            return false;
        }

        //Check if the current user is in the group
        $userIsInGroup = $DB->record_exists('lc_group_members', ['userid' => $USER->id, 'groupid' => $groupid]);
        if (!$userIsInGroup) {
            // Return for now. Maybe throw exception or something later
            return false;
        }

        //If there is a request for joining this group, delete it
        $DB->delete_records('lc_group_requests', ['userid' => $userid, 'groupid' => $groupid]);

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
        $keywords = $DB->get_records('lc_keywords', null, '', 'keyword');
        return array_keys($keywords);
    }

    /**
     * @param $data
     * @return void
     * @throws \dml_exception
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
            $groupid = $DB->insert_record('lc_groups', $record);
            $context = \context_system::instance();
            $options = [];
            $data = file_postupdate_standard_editor($data, 'description', $options, $context, 'local_learningcompanions', 'description', $groupid);
            $DB->set_field('lc_groups', 'description', $data->description, array('id' => $groupid));
            self::save_group_image($groupid, $data->groupimage);
            self::group_assign_keywords($groupid, $data->keywords);
            self::group_add_member($groupid, $USER->id, 1);
            self::create_group_chat($groupid);
            $transaction->allow_commit();
        } catch (\Exception $e) {
            $transaction->rollback($e);
        }
    }

    /**
     * @param $keyword
     * @return false|mixed
     * @throws \dml_exception
     */
    public static function keyword_get_id($keyword) {
        global $DB;
        return $DB->get_field('lc_keywords', 'id', ['keyword' => $keyword]);
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
        global $DB;
        // ICTODO: make sure that user may update this group
        $group = $DB->get_record('lc_groups', ['id' => $groupid], '*', MUST_EXIST);
        $group->name = $name;
        $group->description = $description;
        $group->closedgroup = $closedgroup;
        $group->timemodified = time();
        $group->courseid = $courseid;
        $group->cmid = $cmid;
        $DB->update_record('lc_groups', $group);
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
        file_save_draft_area_files($image, $context->id, 'local_learningcompanions', 'groupimage', $groupid, create_edit_group_form::get_filepickeroptions());

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
        return $DB->insert_record('lc_group_members', $record);
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
        $DB->insert_record('lc_groups_keywords', $obj);
    }


    /**
     * @param $keyword
     * @return bool|int|mixed
     * @throws \dml_exception
     */
    protected static function keyword_create($keyword) {
        global $DB;
        if ($DB->record_exists('lc_keywords', array('keyword' => $keyword))) {
            return $DB->get_field('lc_keywords', 'id', array('keyword' => $keyword));
        }
        $obj = new \stdClass();
        $obj->keyword = $keyword;
        return $DB->insert_record('lc_keywords', $obj);
    }


    /**
     * @param $groupid
     * @return void
     * @throws \dml_exception
     */
    protected static function group_remove_all_keywords($groupid) {
        global $DB;
        $DB->delete_records('lc_groups_keywords', array('groupid' => $groupid));
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
        $record->course = $DB->get_field('lc_groups', 'courseid', array('id' => $groupid));
        $DB->insert_record('lc_chat', $record);
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
        $deleted = $DB->delete_records('lc_group_members', ['groupid' => $groupId, 'userid' => $userId]);
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
        $DB->set_field('lc_group_members', 'isadmin', 1, ['groupid' => $groupId, 'userid' => $userId]);

        messages::send_appointed_to_admin_notification($userId, $groupId, $USER->id);
    }

    public static function unmake_admin(int $userId, int $groupId) {
        global $DB;
        $DB->set_field('lc_group_members', 'isadmin', 0, ['groupid' => $groupId, 'userid' => $userId]);
    }

    /**
     * @param $userId
     * @param $groupId
     *
     * @return bool|int
     * @throws \dml_exception
     */
    public static function request_group_join($userId, $groupId) {
        // Check if the group is closed
        $group = new group($groupId);

        // If the group is not closed, there is no need to request to join. Just join
        if (!$group->closedgroup) {
            return self::group_add_member($groupId, $userId);
        }

        $inserted = self::add_group_join_request($groupId, $userId);

        if ($inserted) {
            foreach ($group->admins as $admin) {
                messages::send_group_join_requested_notification($userId, $admin->id, $groupId);
            }
        }

        return $inserted;
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

        $requests = $DB->get_records_sql('SELECT * FROM {lc_group_requests} WHERE groupid IN (' . implode(',', $groupIds) . ') and denied = 0') ?? [];
        $requestedUsersIds = array_map(static function($request) {
            return $request->userid;
        }, $requests);
        $requestedUsers = $DB->get_records_list('user', 'id', $requestedUsersIds) ?? [];

        foreach ($requests as $request) {
            $request->user = $requestedUsers[$request->userid];
        }

        return $requests;
    }

    protected static function add_group_join_request($groupId, $userId) {
        global $DB;
        $record = new \stdClass();
        $record->groupid = $groupId;
        $record->userid = $userId;
        $record->timecreated = time();
        return $DB->insert_record('lc_group_requests', $record);
    }

    public static function accept_group_join_request($requestId) {
        global $DB;

        //Get the request
        $request = $DB->get_record('lc_group_requests', ['id' => $requestId]);
        //Add the user to the group
        self::group_add_member($request->groupid, $request->userid);
        //Delete the request
        $DB->delete_records('lc_group_requests', ['id' => $requestId]);
        messages::send_group_join_accepted_notification($request->userid, $request->groupid);
    }

    public static function deny_group_join_request($requestId) {
        global $DB;
        $request = $DB->get_record('lc_group_requests', ['id' => $requestId]);

        $DB->set_field('lc_group_requests', 'denied', 1, ['id' => $requestId]);
        messages::send_group_join_denied_notification($request->userid, $request->groupid);
    }

    /**
     * @param int $userId
     * @param int $groupId
     *
     * @return bool
     * @throws \dml_exception
     */
    public static function join_group(int $userId, int $groupId) {
        // Check if the group is open
        $group = new group($groupId);
        // If the group is not open, there is no need to join. Just request to join
        if ($group->closedgroup) {
            return self::request_group_join($userId, $groupId);
        }
        return self::group_add_member($groupId, $userId);
    }

    public static function delete_group(int $groupId) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        // Delete the group members
        $DB->delete_records('lc_group_members', ['groupid' => $groupId]);
        // Delete the group requests
        $DB->delete_records('lc_group_requests', ['groupid' => $groupId]);
        // Delete the group keywords
        $DB->delete_records('lc_groups_keywords', ['groupid' => $groupId]);
        // Delete the group
        $DB->delete_records('lc_groups', ['id' => $groupId]);
        //Get Chat ID
        $chatId = $DB->get_field('lc_chat', 'id', ['chattype' => self::CHATTYPE_GROUP, 'relatedid' => $groupId]);
        //Delete Chat
        $DB->delete_records('lc_chat', ['id' => $chatId]);
        //Delete Chat Messages
        $DB->delete_records('lc_chat_comment', ['chatid' => $chatId]);
        $transaction->allow_commit();
    }

    public static function count_comments_since_last_visit($groupid) {
        global $DB, $USER;
        $chatId = $DB->get_field('lc_chat', 'id', ['chattype' => self::CHATTYPE_GROUP, 'relatedid' => $groupid]);
        $lastVisited = $DB->get_field('lc_chat_lastvisited', 'timevisited', array('chatid' => $chatId, 'userid' => $USER->id));
        if (false === $lastVisited) {
            $lastVisited = 0;
        }
        $commentsSinceLastVisit = $DB->get_records_sql(
            'SELECT * FROM {lc_chat_comment}
                    WHERE chatid = ? AND timecreated > ?',
            array($chatId, $lastVisited)
        );
        if (false === $commentsSinceLastVisit) {
            return 0;
        }
        return count($commentsSinceLastVisit);
    }
}
