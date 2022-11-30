<?php
namespace local_learningcompanions;
include_once __DIR__ . "/groups.php";
require_once dirname(__DIR__). '/lib.php';

class group {
    public int $id;
    public $admins;
    public string $createdby_fullname;
    public string $createdby_profileurl;
    public $keywords;
    public string $keywords_list;
    public int $timecreated;
    public string $timecreated_dmY;
    public string $timecreated_userdate;
    public $timemodified;
    public string $timemodified_dmY;
    public string $timemodified_userdate;
    public bool $closedgroup;
    public string $closedgroupicon;
    public string $description;
    public string $shortdescription;
    public string $name;
    public int $courseid;
    public int $cmid;
    public object $course;
    public object $cm;
    public $imageurl = null;
    public $image = null;
    public int $userid;
    public $groupmembers = null;
    public $membercount = null;
    protected $earliestpost = null;
    protected $latestpost = null;
    protected $myearliestpost = null;
    protected $mylatestpost = null;
    public $currentUserIsMember;


    public function __construct($groupid, $userid = null) {
        global $DB, $CFG, $USER;
        if (is_null($userid)) {
            $userid = $USER->id;
        }
        $this->userid = $userid;
        $this->id = $groupid;
        $group = $DB->get_record('lc_groups', array('id' => $groupid));
        $user = $DB->get_record('user', array('id' => $group->createdby));
        foreach($group as $key => $value) {
            $this->$key = $value;
        }

        $this->createdby_fullname = fullname($user);
        $this->createdby_profileurl = $CFG->wwwroot.'/user/profile.php?id='.$user->id;

        $this->timecreated_userdate = userdate($this->timecreated);
        $this->timecreated_dmY = date('d.m.Y', $this->timecreated);
        $this->timemodified_userdate = userdate($this->timemodified);
        $this->timemodified_dmY = date('d.m.Y', $this->timemodified);
        $this->closedgroupicon = $this->closedgroup == 1 ? '<i class="icon fa fa-check"></i>' : '';
        $shortdescription = strip_tags($this->description);
        $this->shortdescription = substr($shortdescription, 0, 50);
        if (strlen($shortdescription) > 50) {
            $this->shortdescription .= " ...";
        }

        $this->get_image();
        $this->get_imageurl();
        $this->get_groupmembers();
        $this->get_membercount();
        $this->get_admins();
//        $this->get_keywords();
        $this->get_keywords_list();

        // ICTODO: fetch course and course category along with relevant metadata from course and course category, like topic and such
    }

    /**
     * use magic functions, so we can access data that only needs to be read on the fly without calling methods
     * lazy loading type of thing
     * @param $name
     * @return array|int|void
     * @throws \dml_exception
     */
    public function __get($name) {
        $methodname = "get_" . $name;
        if (property_exists($this, $name) && method_exists($this, $methodname)) {
            return $this->$methodname();
        }
        return null;
    }

    /**
     * @return int
     * @throws \dml_exception
     */
    protected function get_latestpost() {
        global $DB;
        if (!is_null($this->latestpost)) {
            return $this->latestpost;
        }
        $query = "SELECT MAX(posts.timecreated) AS latestpost,
                    FROM {lc_chat} chat ON chat.relatedid = ? AND chat.chattype = 1
               LEFT JOIN {lc_chat_comment} posts ON posts.chatid = chat.id
                                                 GROUP BY chat.id";
        $result = $DB->get_record_sql($query, array($this->id));
        if (!$result) {
            $this->latestpost = 0;
        } else {
            $this->latestpost = $result->latestpost;
        }
        return $this->latestpost;
    }

    /**
     * @return int
     * @throws \dml_exception
     */
    protected function get_earliestpost() {
        global $DB;
        if (!is_null($this->earliestpost)) {
            return $this->earliestpost;
        }
        $query = "SELECT MIN(posts.timecreated) AS earliestpost,
                    FROM {lc_chat} chat ON chat.relatedid = ? AND chat.chattype = 1
               LEFT JOIN {lc_chat_comment} posts ON posts.chatid = chat.id
                                                 GROUP BY chat.id";
        $result = $DB->get_record_sql($query, array($this->id));
        if (!$result) {
            $this->earliestpost = 0;
        } else {
            $this->earliestpost = $result->earliestpost;
        }
        return $this->earliestpost;
    }

    /**
     * @return int
     * @throws \dml_exception
     */
    protected function get_mylatestpost() {
        global $DB;
        if (!is_null($this->mylatestpost)) {
            return $this->mylatestpost;
        }
        $query = "SELECT MAX(posts.timecreated) AS latestpost,
                    FROM {lc_chat} chat ON chat.relatedid = ? AND chat.chattype = 1
               LEFT JOIN {lc_chat_comment} posts ON posts.chatid = chat.id AND posts.userid = ?
                                                 GROUP BY chat.id";
        $result = $DB->get_record_sql($query, array($this->id, $this->userid));
        if (!$result) {
            $this->mylatestpost = 0;
        } else {
            $this->mylatestpost = $result->mylatestpost;
        }
        return $this->mylatestpost;
    }

    /**
     * @return int
     * @throws \dml_exception
     */
    protected function get_myearliestpost() {
        global $DB;
        if (!is_null($this->myearliestpost)) {
            return $this->myearliestpost;
        }
        $query = "SELECT MIN(posts.timecreated) AS earliestpost,
                    FROM {lc_chat} chat ON chat.relatedid = ? AND chat.chattype = 1
               LEFT JOIN {lc_chat_comment} posts ON posts.chatid = chat.id AND posts.userid = ?
                                                 GROUP BY chat.id";
        $result = $DB->get_record_sql($query, array($this->id, $this->userid));
        if (!$result) {
            $this->myearliestpost = 0;
        } else {
            $this->myearliestpost = $result->myearliestpost;
        }
        return $this->myearliestpost;
    }

    /**
     * @return array
     * @throws \dml_exception
     */
    protected function get_groupmembers() {
        global $USER;
        if (!is_null($this->groupmembers)) {
            return $this->groupmembers;
        }
        global $DB;
        $groupmembers = $DB->get_records_sql(
            'SELECT DISTINCT u.*, gm.isadmin
                    FROM {user} u
                    JOIN {lc_group_members} gm ON gm.userid = u.id AND u.deleted = 0
                   WHERE gm.groupid = ?',
            array($this->id)
        );
        foreach($groupmembers as $key => $member) {
            $groupmembers[$key]->password = '';
        }
        $this->groupmembers = $groupmembers;
        if (array_key_exists($USER->id, $this->groupmembers)) {
            $this->currentUserIsMember = true;
        }
        return $this->groupmembers;
    }

    /**
     * @return int
     * @throws \dml_exception
     */
    protected function get_membercount() {
        if (!is_null($this->membercount)) {
            return $this->membercount;
        }
        $members = $this->get_groupmembers();
        if (!is_countable($members)) {
            $this->membercount = 0;
        } else {
            $this->membercount = count($members);
        }
        return $this->membercount;
    }

    /**
     * @return array
     * @throws \dml_exception
     */
    protected function get_keywords() {
        if (!is_null($this->keywords)) {
            return $this->keywords;
        }
        global $DB;
        $keywords = $DB->get_records_sql(
            'SELECT DISTINCT k.keyword
                    FROM {lc_keywords} k
                    JOIN {lc_groups_keywords} gk ON gk.groupid = ? AND gk.keywordid = k.id',
            array($this->id)
        );
        $this->keywords = array_keys($keywords);
        return $this->keywords;
    }

    public function get_keywords_list() {
        $keywords_list = $this->get_keywords();
        $keywords_list = implode(', ', $keywords_list);
        $this->keywords_list = $keywords_list;
        return $keywords_list;
    }

    /**
     * @return object|\stored_file|null
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function get_image() {
        if (!is_null($this->image)) {
            return $this->image;
        }
        $context = \context_system::instance();
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'local_learningcompanions', 'groupimage', $this->id);
        foreach ($files as $f) {
            if ($f->is_valid_image()) {
                $this->image = $f;
                return $f;
            }
        }
        $this->image = false;
        return false;
    }

    /**
     * @return \moodle_url|string
     */
    protected function get_imageurl() {
        if (!is_null($this->imageurl)) {
            return $this->imageurl;
        }
        $file = $this->get_image();
        if (!($file instanceof \stored_file)) {
            $this->imageurl = '';
            return '';
        }
        $imageurl = \moodle_url::make_file_url('/pluginfile.php', "/" . $file->get_contextid() . "/local_learningcompanions/groupimage/" .
            $file->get_itemid() . "/" . $file->get_filename());
        $this->imageurl = (String)$imageurl;
        return $this->imageurl;
    }

    /**
     * @return false|mixed|object|\stdClass|null
     * @throws \dml_exception
     */
    protected function get_course() {
        global $DB;
        if (!is_null($this->course)) {
            return $this->course;
        }
        if (!empty($group->courseid)) {
            $this->course = $DB->get_record('course', array('id' => $group->courseid));
        } else {
            $this->course = false;
        }
        return $this->course;
    }

    /**
     * @return false|object|\stdClass|null
     * @throws \coding_exception
     */
    protected function get_cm() {
        if (!is_null($this->cm)) {
            return $this->cm;
        }
        if (!empty($this->cmid)) {
            $this->cm = get_coursemodule_from_id($this->cmid);
        } else {
            $this->cm = false;
        }
        return $this->cm;
    }

    /**
     * @return array
     * @throws \dml_exception
     */
    protected function get_admins() {
        if (!is_null($this->admins)) {
            return $this->admins;
        }
        global $DB, $CFG;

        $sql = 'SELECT u.*,
                       gm.joined
                  FROM {lc_group_members} gm
             LEFT JOIN {user} u ON u.id = gm.userid
                 WHERE gm.groupid = ?
                   AND gm.isadmin = 1';

        $admins = $DB->get_records_sql($sql, [$this->id]);
        foreach ($admins as $admin) {
            $admin->fullname = fullname($admin);
            $admin->profileurl = $CFG->wwwroot.'/user/profile.php?id='.$admin->id;
            $admin->password = '';
            $admin->status = get_user_status($admin->id);
        }

        $this->admins = array_values($admins);
        return $this->admins;
    }
}
