<?php
namespace local_learningcompanions;
include_once __DIR__ . "/group.php";
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
    public static function get_groups_of_user($userid, $sortby = 'latestcomment') {
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
                });                break;
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
        return $return;
    }

    public static function invite_user_to_group($userid, $groupid) {
        // ICTODO: send invitation
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
        $groupid = $DB->insert_record('lc_groups', $record);
        $context = \context_system::instance();
        $options = [];
        $data = file_postupdate_standard_editor($data, 'description', $options, $context, 'local_learningcompanions', 'description', $groupid);
        $DB->set_field('lc_groups', 'description', $data->description, array('id' => $groupid));
        self::save_group_image($groupid, $data->groupimage);
        self::group_assign_keywords($groupid, $data->keywords);
        self::group_add_member($groupid, $USER->id, 1);
        self::create_group_chat($groupid);
    }

    /**
     * @param $keyword
     * @return false|mixed
     * @throws \dml_exception
     */
    public static function keyword_get_id($keyword) {
        global $DB;
        return $DB->get_field('lc_keywords', 'id', array('keyword' => $keyword));
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
        $group = $DB->get_record('lc_groups', array('id' => $groupid), '*', MUST_EXIST);
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
     * @param $groupid
     * @param $userid
     * @param $isadmin
     * @return void
     * @throws \dml_exception
     */
    protected static function group_add_member($groupid, $userid, $isadmin = 0) {
        global $DB;
        $record = new \stdClass();
        $record->groupid = $groupid;
        $record->userid = $userid;
        $record->isadmin = $isadmin;
        $record->joined = time();
        $DB->insert_record('lc_group_members', $record);
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
        $record->courseid = $DB->get_field('lc_groups', 'courseid', array('id' => $groupid));
        $DB->insert_record('lc_chat', $record);
    }
}
