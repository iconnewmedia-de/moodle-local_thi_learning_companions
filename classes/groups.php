<?php
namespace local_learningcompanions;

class groups {

    /**
     * @param int $groupid
     * @return false|mixed|\stdClass
     * @throws \dml_exception
     */
    public static function get_group_by_id($groupid) {
        global $DB;
        $group = $DB->get_record('lc_groups', array('id' => $groupid));
        $group->groupmembers = self::get_group_members($groupid);
        $group->keywords = self::get_group_keywords($groupid);
        // ICTODO: fetch course and course category along with relevant metadata from course and course category, like topic and such
        return $group;
    }

    /**
     * @param bool $extended
     * @param bool $cutdescription
     * @return array
     * @throws \dml_exception
     */
    public static function get_all_groups(bool $extended = false, bool $cutdescription = false): array {
        global $DB;

        $groups = $DB->get_records('lc_groups');
        if ($extended) {
            return self::add_extended_fields_to_groups($groups, $cutdescription);
        }
        return $groups;
    }

    /**
     * @param array $groups
     * @param bool  $cutdescription
     * @return array
     * @throws \dml_exception
     */
    public static function add_extended_fields_to_groups(array $groups, bool $cutdescription = false): array {
        global $CFG, $DB;

        foreach ($groups as $group) {
            $user = $DB->get_record('user', array('id' => $group->createdby));
            $group->createdby_fullname = fullname($user);
            $group->createdby_profileurl = $CFG->wwwroot.'/user/profile.php?id='.$user->id;

            $group->membercount = count(self::get_group_members($group->id));
            $group->admins = self::get_group_admins($group->id, true);
            $group->timecreated_dmY = date('d.m.Y', $group->timecreated);
            $group->closedgroupicon = $group->closedgroup == 1 ? '<i class="icon fa fa-check"></i>' : '';

            $group->origindescription = $group->description;
            if ($cutdescription && strlen($group->description) > 50) {
                $group->description = substr($group->description, 0, 50).'...';
                $group->descriptioncut = true;
            } else {
                $group->descriptioncut = false;
            }
            $group->keywords = self::get_group_keywords($group->id);
            $group->image = self::get_group_image($group->id);
        }

        return $groups;
    }

    /**
     * @param int  $groupid
     * @param bool $extended
     * @return array
     * @throws \dml_exception
     */
    public static function get_group_admins(int $groupid, bool $extended = false): array {
        global $DB;

        $sql = 'SELECT u.*,
                       gm.joined                                              
                  FROM {lc_group_members} gm
             LEFT JOIN {user} u ON u.id = gm.userid
                 WHERE gm.groupid = ?
                   AND gm.isadmin = 1';

        $admins = $DB->get_records_sql($sql, array($groupid));

        if ($extended) {
            return self::add_fullnames_to_admins($admins);
        }
        return $admins;
    }

    /**
     * @param array $admins
     * @return array
     */
    public static function add_fullnames_to_admins(array $admins): array {
        global $CFG;

        foreach ($admins as $admin) {
            $admin->fullname = fullname($admin);
            $admin->profileurl = $CFG->wwwroot.'/user/profile.php?id='.$admin->id;
        }

        return array_values($admins);
    }

    /**
     * @param int $groupid
     * @return array
     * @throws \dml_exception
     */
    public static function get_group_members($groupid) {
        global $DB;
        $groupmembers = $DB->get_records_sql(
            'SELECT DISTINCT u.*, gm.isadmin
                    FROM {user} u
                    JOIN {lc_group_members} gm ON gm.userid = u.id AND u.deleted = 0
                   WHERE gm.groupid = ?',
            array($groupid)
        );
        return $groupmembers;
    }

    /**
     * @param int $groupid
     * @return array
     * @throws \dml_exception
     */
    public static function get_group_keywords($groupid) {
        global $DB;
        $keywords = $DB->get_records_sql(
            'SELECT DISTINCT k.keyword
                    FROM {lc_keywords} k
                    JOIN {lc_groups_keywords} gk ON gk.groupid = ? AND gk.keywordid = k.id',
            array($groupid)
        );
        return array_keys($keywords);
    }

    public static function get_groups_of_user($userid, $sortby = 'latestcomment') {
        global $DB;
        $groupCategory = get_config('local_learningcompanions', 'category');
        $subCategories = self::get_all_subcategories($groupCategory);
        switch($sortby) {
            case 'earliestcomment':
                $order = 'ORDER BY posts.created ASC';
                break;
            case 'mylatestcomment':
                $order = 'ORDER BY myposts.created DESC';
                break;
            case 'myearliestcomment':
                $order = 'ORDER BY myposts.created ASC';
                break;
            case 'latestcomment':
            default:
                $order = 'ORDER BY posts.created DESC';
                break;
        }
        list($sqlIN, $params) = $DB->get_in_or_equal(array_keys($subCategories));
        array_unshift($params, $userid);
        $params[] = $groupCategory;
        // ICTODO: refactor this - we probably won't be using courses and forums as a basis
        $query = "SELECT DISTINCT c.*
                    FROM {course} c
                    JOIN {course_categories} cat ON cat.id = c.category
                    JOIN {user_enrolments} en ON en.userid = ?
                    JOIN {enrol} e ON e.id = en.enrolid AND e.courseid = c.id
               LEFT JOIN {forum} f ON c.id = f.course
               LEFT JOIN {forum_discussions} fd ON fd.forum = f.id
               LEFT JOIN {forum_posts} posts ON posts.discussion = fd.id
               LEFT JOIN {forum_posts} myposts ON posts.discussion = fd.id
                   WHERE cat.id " . $sqlIN . "
                      OR cat.id = ? " . $order;
        $groups = $DB->get_records_sql($query, $params);
        return $groups;
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
     * @param $name
     * @param $description
     * @param $closedgroup
     * @param $courseid
     * @param $keywords
     * @return void
     * @throws \dml_exception
     */
    public static function group_create($name, $description, $closedgroup, $keywords, $courseid, $cmid, $image) {
        global $DB, $USER;
        // ICTODO: check if the user has the permission to create a group for this course
        // ICTODO: check if there's already a group with that name by that user for that course - don't create groups that are indistinguishable from eachother
        $record = new \stdClass();
        $record->name = $name;
        if (is_array($description)) {
            $description = $description['text'];
        }
        $record->description = $description;
        $record->closedgroup = $closedgroup;
        $record->courseid = $courseid;
        $record->cmid = $cmid;
        $record->createdby = $USER->id;
        $record->timecreated = time();
        $record->timemodified = 0;
        $groupid = $DB->insert_record('lc_groups', $record);
        self::save_group_image($groupid, $image);
        self::group_assign_keywords($groupid, $keywords);
        self::group_add_member($groupid, $USER->id, 1);
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
     * get the stored file for the group image of a group
     * @param $groupid
     * @return \stored_file|null
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_group_image($groupid) {
        $context = \context_system::instance();
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'local_learningcompanions', 'groupimage', $groupid);
        foreach ($files as $f) {
            if ($f->is_valid_image()) {
                return $f;
            }
        }
        return null;
    }

    /**
     * get the image url for a certain group id
     * @param $groupid
     * @return \moodle_url|string
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_group_image_url($groupid) {
        $file = self::get_group_image($groupid);
        return self::image_to_url($file);
    }

    /**
     * takes a stored file and returns the corresponding image url
     * @param  \stored_file $file
     * @return \moodle_url|string
     */
    public static function image_to_url($file) {
        if (!($file instanceof \stored_file)) {
            return '';
        }
        $imageurl = \moodle_url::make_file_url('/pluginfile.php', "/" . $file->get_contextid() . "/local_learningcompanions/groupimage/" .
            $file->get_itemid() . "/" . $file->get_filename());
        return $imageurl;
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
     * @param $categoryID
     * @return array|mixed
     * @throws \dml_exception
     */
    protected static function get_all_subcategories($categoryID) {
        global $DB;
        $return = $subcategories = $DB->get_records('course_categories', array('parent' => $categoryID));
        foreach($subcategories as $subcategory) {
            $children = self::get_all_subcategories($subcategory->id);
            if (!empty($children)) {
                $return = $return + $children;
            }
        }
        return $return;
    }

    /**
     * @param $groupid
     * @param $image
     * @return void
     * @throws \dml_exception
     */
    protected static function save_group_image($groupid, $image) {
        $context = \context_system::instance();
        file_save_draft_area_files($image, $context->id, 'local_learningcompanions', 'groupimage', $groupid, group_form::get_filepickeroptions());

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
}